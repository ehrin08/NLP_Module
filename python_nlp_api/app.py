import os
from pathlib import Path
from threading import Lock

import joblib
from flask import Flask, jsonify, request

from preprocess import ensure_nltk_resources, preprocess_text
from train_model import MODEL_PATH, VECTORIZER_PATH, train


app = Flask(__name__)
_artifact_lock = Lock()
_model = None
_vectorizer = None


def load_artifacts() -> tuple[object, object]:
    global _model, _vectorizer

    with _artifact_lock:
        if _model is not None and _vectorizer is not None:
            return _model, _vectorizer

        if not Path(MODEL_PATH).exists() or not Path(VECTORIZER_PATH).exists():
            train()

        _model = joblib.load(MODEL_PATH)
        _vectorizer = joblib.load(VECTORIZER_PATH)

    return _model, _vectorizer


@app.get("/health")
def health():
    return jsonify({"status": "ok"})


@app.post("/predict")
def predict():
    payload = request.get_json(silent=True) or {}
    text = payload.get("text", "")

    if not isinstance(text, str) or not text.strip():
        return jsonify({"error": "The text field is required."}), 400

    ensure_nltk_resources()
    model, vectorizer = load_artifacts()
    processed_text = preprocess_text(text)

    if not processed_text:
        return jsonify({"sentiment": "Neutral", "confidence": 0.5})

    features = vectorizer.transform([processed_text])
    probabilities = model.predict_proba(features)[0]
    best_index = probabilities.argmax()

    sentiment = str(model.classes_[best_index])
    confidence = round(float(probabilities[best_index]), 4)

    return jsonify({
        "sentiment": sentiment,
        "confidence": confidence,
    })


if __name__ == "__main__":
    load_artifacts()
    app.run(host="127.0.0.1", port=5000, debug=os.getenv("FLASK_DEBUG") == "1")
