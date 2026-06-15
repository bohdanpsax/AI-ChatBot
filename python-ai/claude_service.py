import os
import json
import httpx
from dotenv import load_dotenv

load_dotenv()

ANTHROPIC_API_URL    = "https://api.anthropic.com/v1/messages"
MODEL                = "claude-sonnet-4-6"
MAX_TOKENS           = 1024
CONFIDENCE_THRESHOLD = 0.5

UNCERTAINTY_SIGNALS = [
    "i don't know",
    "i'm not sure",
    "i cannot find",
    "no information",
    "please contact",
    "ik weet het niet",
    "ich weiß nicht",
    "je ne sais pas",
]


class ClaudeService:
    def __init__(self):
        self.api_key = os.getenv("ANTHROPIC_API_KEY")
        if not self.api_key:
            raise ValueError("ANTHROPIC_API_KEY environment variable not set")

    async def chat(
        self,
        message:       str,
        system_prompt: str,
        history:       list[dict] = [],
    ) -> dict:
        messages = self._build_messages(history, message)

        try:
            async with httpx.AsyncClient(timeout=25.0) as client:
                response = await client.post(
                    ANTHROPIC_API_URL,
                    headers={
                        "x-api-key":         self.api_key,
                        "anthropic-version": "2023-06-01",
                        "content-type":      "application/json",
                    },
                    json={
                        "model":      MODEL,
                        "max_tokens": MAX_TOKENS,
                        "system":     system_prompt,
                        "messages":   messages,
                    },
                )
                response.raise_for_status()
                data = response.json()

        except httpx.HTTPStatusError as e:
            return self._error_response(f"API error: {e.response.status_code}")
        except httpx.TimeoutException:
            return self._error_response("API timeout")
        except Exception as e:
            return self._error_response(str(e))

        raw_text    = data["content"][0]["text"]
        tokens_used = data.get("usage", {}).get("output_tokens", 0)

        parsed      = self._parse_response(raw_text)
        reply       = parsed.get("reply", raw_text)
        related     = parsed.get("related_questions", [])
        escalation  = parsed.get("show_escalation", False)

        confidence   = self._score_confidence(reply)
        was_fallback = confidence < CONFIDENCE_THRESHOLD

        if was_fallback:
            reply = self._append_fallback_hint(reply)
            escalation = True

        return {
            "reply":              reply,
            "related_questions":  related,
            "show_escalation":    escalation,
            "was_fallback":       was_fallback,
            "confidence_score":   confidence,
            "tokens_used":        tokens_used,
            "metadata": {
                "model":        MODEL,
                "input_tokens": data.get("usage", {}).get("input_tokens", 0),
            },
        }

    def _parse_response(self, raw: str) -> dict:
        try:
            # Claude sometimes wraps JSON in ```json ... ```
            text = raw.strip()
            if text.startswith("```"):
                text = text.split("```")[1]
                if text.startswith("json"):
                    text = text[4:]
            return json.loads(text.strip())
        except Exception:
            return {"reply": raw, "related_questions": [], "show_escalation": False}

    def _build_messages(self, history: list[dict], new_message: str) -> list[dict]:
        messages = [m for m in history if m.get("role") in ("user", "assistant")]
        messages.append({"role": "user", "content": new_message})
        return messages

    def _score_confidence(self, reply: str) -> float:
        reply_lower = reply.lower()
        hits = sum(1 for signal in UNCERTAINTY_SIGNALS if signal in reply_lower)
        if hits == 0:
            return 1.0
        elif hits == 1:
            return 0.6
        elif hits == 2:
            return 0.4
        else:
            return 0.2

    def _append_fallback_hint(self, reply: str) -> str:
        hint = "\n\n📞 For further assistance, please contact our reception directly."
        return reply + hint if hint not in reply else reply

    def _error_response(self, reason: str) -> dict:
        return {
            "reply":             "I'm sorry, I'm unable to answer right now. Please contact reception.",
            "related_questions": [],
            "show_escalation":   True,
            "was_fallback":      True,
            "confidence_score":  0.0,
            "tokens_used":       0,
            "metadata":          {"error": reason},
        }