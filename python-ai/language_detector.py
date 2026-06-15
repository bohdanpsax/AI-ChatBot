import re
from collections import Counter

LANGUAGE_SIGNATURES: dict[str, list[str]] = {
    "nl": ["de", "het", "een", "is", "ik", "je", "we", "niet", "wat", "hoe", "en", "of", "in", "op", "met", "zijn", "voor", "aan"],
    "de": ["ich", "du", "ist", "das", "die", "der", "ein", "und", "mit", "für", "auf", "von", "zu", "wir", "sie", "nicht", "was", "wie"],
    "fr": ["je", "tu", "il", "est", "les", "des", "une", "et", "ou", "en", "sur", "pour", "avec", "pas", "vous", "nous", "qui", "que"],
    "es": ["yo", "es", "los", "las", "una", "con", "para", "por", "que", "en", "su", "del", "al", "son", "como", "más"],
    "it": ["io", "è", "gli", "una", "con", "per", "che", "non", "si", "la", "le", "un", "del", "al", "sono", "come"],
    "pl": ["jest", "nie", "się", "jak", "czy", "ale", "tak", "że", "do", "na", "za", "po", "przy", "przez"],
    "en": ["the", "is", "are", "you", "we", "do", "can", "have", "this", "that", "what", "how", "where", "when", "and", "or", "not"],
}

CYRILLIC_PATTERN = re.compile(r'[\u0400-\u04FF]')


class LanguageDetector:

    def detect(self, text: str, fallback: str = "en") -> str:
        text = text.strip().lower()

        if not text:
            return fallback

        if CYRILLIC_PATTERN.search(text):
            return "ru"

        words = re.findall(r'\b[a-z]{2,}\b', text)
        if not words:
            return fallback

        word_set = set(words)
        scores: Counter = Counter()

        for lang, signatures in LANGUAGE_SIGNATURES.items():
            hits = sum(1 for w in signatures if w in word_set)
            if hits:
                scores[lang] = hits

        if not scores:
            return fallback

        best_lang, best_score = scores.most_common(1)[0]

        if best_score < 2:
            return fallback

        return best_lang