from pathlib import Path

import joblib
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.metrics import accuracy_score, classification_report, confusion_matrix
from sklearn.model_selection import train_test_split

from preprocess import ensure_nltk_resources, preprocess_text


BASE_DIR = Path(__file__).resolve().parent
DATASET_PATH = BASE_DIR / "sample_feedback_dataset.csv"
MODEL_PATH = BASE_DIR / "sentiment_model.pkl"
VECTORIZER_PATH = BASE_DIR / "vectorizer.pkl"
VALID_SENTIMENTS = {"Positive", "Neutral", "Negative"}


def load_dataset(dataset_path: Path = DATASET_PATH) -> pd.DataFrame:
    dataset = pd.read_csv(dataset_path)

    missing_columns = {"feedback", "sentiment"} - set(dataset.columns)
    if missing_columns:
        missing = ", ".join(sorted(missing_columns))
        raise ValueError(f"Dataset is missing required column(s): {missing}")

    dataset = dataset[["feedback", "sentiment"]].dropna()
    dataset["sentiment"] = dataset["sentiment"].astype(str).str.strip().str.title()
    dataset = dataset[dataset["sentiment"].isin(VALID_SENTIMENTS)]

    if dataset.empty:
        raise ValueError("Dataset does not contain valid training rows.")

    return dataset


def train(dataset_path: Path = DATASET_PATH) -> dict[str, object]:
    ensure_nltk_resources()

    dataset = load_dataset(dataset_path)
    dataset["clean_feedback"] = dataset["feedback"].astype(str).apply(preprocess_text)
    dataset = dataset[dataset["clean_feedback"].str.len() > 0]

    if dataset["sentiment"].nunique() < 3:
        raise ValueError("Dataset must include Positive, Neutral, and Negative sentiment rows.")

    X_train, X_test, y_train, y_test = train_test_split(
        dataset["clean_feedback"],
        dataset["sentiment"],
        test_size=0.2,
        random_state=42,
        stratify=dataset["sentiment"],
    )

    vectorizer = TfidfVectorizer(ngram_range=(1, 2), min_df=1, max_features=5000, sublinear_tf=True)
    X_train_vectorized = vectorizer.fit_transform(X_train)
    X_test_vectorized = vectorizer.transform(X_test)

    model = LogisticRegression(max_iter=1000, class_weight="balanced", C=2.0)
    model.fit(X_train_vectorized, y_train)

    predictions = model.predict(X_test_vectorized)
    accuracy = accuracy_score(y_test, predictions)
    report = classification_report(y_test, predictions, zero_division=0)
    matrix = confusion_matrix(y_test, predictions, labels=sorted(VALID_SENTIMENTS))

    joblib.dump(model, MODEL_PATH)
    joblib.dump(vectorizer, VECTORIZER_PATH)

    return {
        "rows": int(len(dataset)),
        "accuracy": float(accuracy),
        "classification_report": report,
        "confusion_matrix": matrix.tolist(),
        "labels": sorted(VALID_SENTIMENTS),
        "model_path": str(MODEL_PATH),
        "vectorizer_path": str(VECTORIZER_PATH),
    }


if __name__ == "__main__":
    results = train()
    print(f"Rows used: {results['rows']}")
    print(f"Accuracy: {results['accuracy']:.4f}")
    print(results["classification_report"])
    print("Labels:", ", ".join(results["labels"]))
    print("Confusion matrix:")
    print(results["confusion_matrix"])
    print(f"Saved model: {results['model_path']}")
    print(f"Saved vectorizer: {results['vectorizer_path']}")
