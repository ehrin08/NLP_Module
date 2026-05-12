from pathlib import Path

import joblib
from sklearn.metrics import classification_report, confusion_matrix
from sklearn.model_selection import train_test_split

from preprocess import preprocess_text
from train_model import DATASET_PATH, MODEL_PATH, VALID_SENTIMENTS, VECTORIZER_PATH, load_dataset


def evaluate(dataset_path: Path = DATASET_PATH) -> dict[str, object]:
    dataset = load_dataset(dataset_path)
    dataset["clean_feedback"] = dataset["feedback"].astype(str).apply(preprocess_text)
    dataset = dataset[dataset["clean_feedback"].str.len() > 0]

    _, X_test, _, y_test = train_test_split(
        dataset["clean_feedback"],
        dataset["sentiment"],
        test_size=0.2,
        random_state=42,
        stratify=dataset["sentiment"],
    )

    model = joblib.load(MODEL_PATH)
    vectorizer = joblib.load(VECTORIZER_PATH)
    predictions = model.predict(vectorizer.transform(X_test))
    labels = sorted(VALID_SENTIMENTS)

    return {
        "labels": labels,
        "classification_report": classification_report(y_test, predictions, labels=labels, zero_division=0),
        "confusion_matrix": confusion_matrix(y_test, predictions, labels=labels).tolist(),
    }


if __name__ == "__main__":
    results = evaluate()
    print("Labels:", ", ".join(results["labels"]))
    print("Classification report:")
    print(results["classification_report"])
    print("Confusion matrix:")
    print(results["confusion_matrix"])
