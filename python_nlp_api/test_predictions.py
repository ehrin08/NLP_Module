import unittest

from app import load_artifacts
from preprocess import preprocess_text


class TaglishPredictionTest(unittest.TestCase):
    @classmethod
    def setUpClass(cls) -> None:
        cls.model, cls.vectorizer = load_artifacts()

    def predict(self, text: str) -> tuple[str, float]:
        features = self.vectorizer.transform([preprocess_text(text)])
        probabilities = self.model.predict_proba(features)[0]
        best_index = probabilities.argmax()

        return str(self.model.classes_[best_index]), float(probabilities[best_index])

    def assertPrediction(self, text: str, expected_sentiment: str) -> None:
        sentiment, confidence = self.predict(text)

        self.assertEqual(sentiment, expected_sentiment, f"{text} predicted as {sentiment} ({confidence:.4f})")
        self.assertGreaterEqual(confidence, 0.45, f"{text} confidence too low: {confidence:.4f}")

    def test_positive_taglish_samples(self) -> None:
        samples = [
            "Super relaxing ng massage.",
            "Sulit yung service.",
            "Ang bait ng staff.",
            "Highly recommended talaga.",
        ]

        for sample in samples:
            with self.subTest(sample=sample):
                self.assertPrediction(sample, "Positive")

    def test_negative_taglish_samples(self) -> None:
        samples = [
            "Matagal bago kami naasikaso.",
            "Hindi worth it yung presyo.",
            "Di ako satisfied sa service.",
            "Mainit yung room.",
        ]

        for sample in samples:
            with self.subTest(sample=sample):
                self.assertPrediction(sample, "Negative")

    def test_neutral_taglish_samples(self) -> None:
        samples = [
            "Okay lang naman.",
            "Average experience.",
            "Pwede na.",
            "Sakto lang yung massage.",
        ]

        for sample in samples:
            with self.subTest(sample=sample):
                self.assertPrediction(sample, "Neutral")


if __name__ == "__main__":
    unittest.main()
