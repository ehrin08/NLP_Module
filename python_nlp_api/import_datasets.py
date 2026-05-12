import argparse
from pathlib import Path

import pandas as pd

from train_model import DATASET_PATH, VALID_SENTIMENTS, train


FEEDBACK_ALIASES = ["feedback", "text", "review", "comment", "feedback_text", "message"]
SENTIMENT_ALIASES = ["sentiment", "label", "category", "polarity", "predicted_sentiment"]
LABEL_MAP = {
    "pos": "Positive",
    "positive": "Positive",
    "good": "Positive",
    "1": "Positive",
    "neu": "Neutral",
    "neutral": "Neutral",
    "ok": "Neutral",
    "okay": "Neutral",
    "0": "Neutral",
    "neg": "Negative",
    "negative": "Negative",
    "bad": "Negative",
    "-1": "Negative",
}


def resolve_input_paths(inputs: list[str]) -> list[Path]:
    paths: list[Path] = []

    for input_path in inputs:
        path = Path(input_path).expanduser().resolve()

        if path.is_dir():
            paths.extend(sorted(path.glob("*.csv")))
        elif path.is_file() and path.suffix.lower() == ".csv":
            paths.append(path)
        else:
            raise FileNotFoundError(f"CSV file or directory not found: {input_path}")

    if not paths:
        raise ValueError("No CSV files found to import.")

    return paths


def find_column(columns: list[str], aliases: list[str]) -> str | None:
    normalized_columns = {column.lower().strip(): column for column in columns}

    for alias in aliases:
        if alias in normalized_columns:
            return normalized_columns[alias]

    return None


def normalize_dataset(path: Path) -> pd.DataFrame:
    dataset = pd.read_csv(path)
    feedback_column = find_column(list(dataset.columns), FEEDBACK_ALIASES)
    sentiment_column = find_column(list(dataset.columns), SENTIMENT_ALIASES)

    if feedback_column is None or sentiment_column is None:
        raise ValueError(
            f"{path.name} must include feedback/text and sentiment/label columns."
        )

    normalized = dataset[[feedback_column, sentiment_column]].rename(
        columns={feedback_column: "feedback", sentiment_column: "sentiment"}
    )
    normalized = normalized.dropna()
    normalized["feedback"] = normalized["feedback"].astype(str).str.strip()
    normalized["sentiment"] = (
        normalized["sentiment"]
        .astype(str)
        .str.strip()
        .str.lower()
        .map(LABEL_MAP)
        .fillna(normalized["sentiment"].astype(str).str.strip().str.title())
    )
    normalized = normalized[
        normalized["feedback"].str.len().gt(0)
        & normalized["sentiment"].isin(VALID_SENTIMENTS)
    ]

    return normalized[["feedback", "sentiment"]]


def import_datasets(
    inputs: list[str],
    output_path: Path = DATASET_PATH,
    replace: bool = False,
    dedupe: bool = True,
) -> pd.DataFrame:
    imported = pd.concat(
        [normalize_dataset(path) for path in resolve_input_paths(inputs)],
        ignore_index=True,
    )

    if not replace and output_path.exists():
        existing = pd.read_csv(output_path)
        combined = pd.concat([existing[["feedback", "sentiment"]], imported], ignore_index=True)
    else:
        combined = imported

    combined["feedback"] = combined["feedback"].astype(str).str.strip()
    combined["sentiment"] = combined["sentiment"].astype(str).str.strip().str.title()
    combined = combined[
        combined["feedback"].str.len().gt(0)
        & combined["sentiment"].isin(VALID_SENTIMENTS)
    ]

    if dedupe:
        combined = combined.drop_duplicates(subset=["feedback", "sentiment"], keep="first")

    output_path.parent.mkdir(parents=True, exist_ok=True)
    combined.to_csv(output_path, index=False, encoding="utf-8")

    return combined


def main() -> None:
    parser = argparse.ArgumentParser(description="Import labeled sentiment CSV datasets.")
    parser.add_argument("inputs", nargs="+", help="CSV file(s) or folder(s) containing CSV datasets.")
    parser.add_argument("--output", default=str(DATASET_PATH), help="Output training CSV path.")
    parser.add_argument("--replace", action="store_true", help="Replace output dataset instead of appending.")
    parser.add_argument("--no-dedupe", action="store_true", help="Keep duplicate feedback rows.")
    parser.add_argument("--retrain", action="store_true", help="Retrain model after importing.")
    args = parser.parse_args()

    output_path = Path(args.output).expanduser().resolve()
    combined = import_datasets(
        inputs=args.inputs,
        output_path=output_path,
        replace=args.replace,
        dedupe=not args.no_dedupe,
    )

    counts = combined["sentiment"].value_counts().reindex(sorted(VALID_SENTIMENTS), fill_value=0)
    print(f"Saved dataset: {output_path}")
    print(f"Total rows: {len(combined)}")
    for sentiment, count in counts.items():
        print(f"{sentiment}: {count}")

    if args.retrain:
        results = train(output_path)
        print(f"Retrained model accuracy: {results['accuracy']:.4f}")


if __name__ == "__main__":
    main()
