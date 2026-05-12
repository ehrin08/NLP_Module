import json
import re
from functools import lru_cache
from pathlib import Path

import nltk
from nltk.corpus import stopwords
from nltk.stem import WordNetLemmatizer


BASE_DIR = Path(__file__).resolve().parent
FILIPINO_STOPWORDS_PATH = BASE_DIR / "filipino_stopwords.txt"
NORMALIZATION_DICTIONARY_PATH = BASE_DIR / "normalization_dictionary.json"
NEGATION_TERMS = {"no", "nor", "not", "never", "without", "hindi", "wala", "walang", "ayaw"}

_NLTK_RESOURCES = [
    ("corpora/stopwords", "stopwords"),
    ("corpora/wordnet", "wordnet"),
    ("corpora/omw-1.4", "omw-1.4"),
]


def ensure_nltk_resources() -> None:
    for resource_path, package_name in _NLTK_RESOURCES:
        try:
            nltk.data.find(resource_path)
        except LookupError:
            nltk.download(package_name, quiet=True)


@lru_cache(maxsize=1)
def _english_stopwords() -> set[str]:
    ensure_nltk_resources()

    return set(stopwords.words("english")) - NEGATION_TERMS


@lru_cache(maxsize=1)
def _filipino_stopwords() -> set[str]:
    if not FILIPINO_STOPWORDS_PATH.exists():
        return set()

    words = {
        line.strip().lower()
        for line in FILIPINO_STOPWORDS_PATH.read_text(encoding="utf-8").splitlines()
        if line.strip() and not line.strip().startswith("#")
    }

    return words - NEGATION_TERMS


@lru_cache(maxsize=1)
def _normalization_dictionary() -> dict[str, str]:
    if not NORMALIZATION_DICTIONARY_PATH.exists():
        return {}

    with NORMALIZATION_DICTIONARY_PATH.open("r", encoding="utf-8") as file:
        return {key.lower(): value.lower() for key, value in json.load(file).items()}


@lru_cache(maxsize=1)
def _lemmatizer() -> WordNetLemmatizer:
    ensure_nltk_resources()
    return WordNetLemmatizer()


def _normalize_phrases(text: str, normalization_map: dict[str, str]) -> str:
    normalized = text

    for source, replacement in sorted(normalization_map.items(), key=lambda item: len(item[0]), reverse=True):
        if " " not in source:
            continue

        normalized = re.sub(rf"\b{re.escape(source)}\b", f" {replacement} ", normalized)

    return normalized


def _normalize_token(token: str, normalization_map: dict[str, str]) -> list[str]:
    replacement = normalization_map.get(token, token).strip()

    if not replacement:
        return []

    return re.findall(r"[a-z]+", replacement)


def preprocess_text(text: str) -> str:
    if not isinstance(text, str):
        text = ""

    normalization_map = _normalization_dictionary()
    stop_words = _english_stopwords() | _filipino_stopwords()
    lemmatizer = _lemmatizer()

    normalized = _normalize_phrases(text.lower(), normalization_map)
    normalized = re.sub(r"http\S+|www\S+|@\w+|#\w+", " ", normalized)
    normalized = re.sub(r"[^a-zñ\s]", " ", normalized)
    normalized = re.sub(r"\s+", " ", normalized).strip()

    tokens = re.findall(r"[a-zñ]+", normalized)
    expanded_tokens = []

    for token in tokens:
        expanded_tokens.extend(_normalize_token(token, normalization_map))

    cleaned_tokens = []

    for token in expanded_tokens:
        if len(token) <= 1 or token in stop_words:
            continue

        cleaned_tokens.append(lemmatizer.lemmatize(token))

    return " ".join(cleaned_tokens)
