MAX_FAQ_CHARS = 6000

SYSTEM_TEMPLATE = """\
You are a helpful AI assistant for {campsite_name}, a campsite in the Netherlands.
Answer guest questions in a friendly, concise, and helpful way.

IMPORTANT RULES:
- Always respond in the same language the guest is writing in ({language_instruction})
- If you don't know the answer, say so honestly and suggest contacting reception
- Keep answers short and practical (2-4 sentences max)
- Do not make up information that is not in the context below

You MUST respond with a valid JSON object in this exact format:
{{
  "reply": "your answer here",
  "related_questions": ["question 1", "question 2"],
  "show_escalation": true or false
}}

Rules for the JSON fields:
- "reply": your answer to the guest's question
- "related_questions": 2-3 relevant questions from the FAQ list below that the guest might also want to know. Return empty array [] if none are relevant.
- "show_escalation": true if the guest seems to need more help or the question is complex, false otherwise

=== CAMPSITE INFORMATION ===
Name:          {campsite_name}
Description:   {campsite_description}
Location:      {campsite_location}
Phone:         {campsite_phone}
Email:         {campsite_email}
Check-in:      {checkin_time}
Check-out:     {checkout_time}

=== FREQUENTLY ASKED QUESTIONS ===
{faq_block}
"""

LANGUAGE_NAMES = {
    "en": "English",
    "nl": "Dutch (Nederlands)",
    "de": "German (Deutsch)",
    "fr": "French (François)",
    "es": "Spanish (Español)",
    "it": "Italian (Italiano)",
    "pl": "Polish (Polski)",
    "ru": "Russian (Русский)",
}


class ContextBuilder:

    def build(self, context: dict, language: str) -> str:
        campsite = context.get("campsite", {})
        faqs     = context.get("faqs", [])

        faq_block = self._build_faq_block(faqs)
        lang_name = LANGUAGE_NAMES.get(language, language.upper())

        return SYSTEM_TEMPLATE.format(
            campsite_name        = campsite.get("name", "our campsite"),
            campsite_description = campsite.get("description", ""),
            campsite_location    = campsite.get("location", ""),
            campsite_phone       = campsite.get("phone", ""),
            campsite_email       = campsite.get("email", ""),
            checkin_time         = campsite.get("checkin_time", "14:00"),
            checkout_time        = campsite.get("checkout_time", "11:00"),
            faq_block            = faq_block,
            language_instruction = f"the guest is writing in {lang_name}, respond in {lang_name}",
        )

    def _build_faq_block(self, faqs: list[dict]) -> str:
        if not faqs:
            return "(No FAQs available)"

        lines: list[str] = []
        total_chars = 0

        for faq in faqs:
            entry = f"Q: {faq.get('question', '')}\nA: {faq.get('answer', '')}\n"
            if total_chars + len(entry) > MAX_FAQ_CHARS:
                lines.append("... (additional FAQs truncated to stay within context limit)")
                break
            lines.append(entry)
            total_chars += len(entry)

        return "\n".join(lines)