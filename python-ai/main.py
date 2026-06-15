from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from typing import Optional
import uvicorn
from claude_service import ClaudeService
from language_detector import LanguageDetector
from context_builder import ContextBuilder

app = FastAPI(title="Camping.care AI Service", version="1.0.0")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000"],
    allow_methods=["*"],
    allow_headers=["*"],
)

claude_service  = ClaudeService()
lang_detector   = LanguageDetector()
context_builder = ContextBuilder()


class ChatRequest(BaseModel):
    message:    str
    context:    dict
    history:    list[dict] = []
    session_id: Optional[str] = None


class ChatResponse(BaseModel):
    reply:              str
    detected_language:  str
    related_questions:  list[str] = []
    show_escalation:    bool = False
    was_fallback:       bool
    confidence_score:   Optional[float]
    tokens_used:        int
    metadata:           Optional[dict]


@app.get("/health")
def health():
    return {"status": "ok", "service": "camping-ai-python"}


@app.post("/chat", response_model=ChatResponse)
async def chat(req: ChatRequest):
    detected_lang = lang_detector.detect(req.message)

    system_prompt = context_builder.build(
        context=req.context,
        language=detected_lang,
    )

    result = await claude_service.chat(
        message=req.message,
        system_prompt=system_prompt,
        history=req.history,
    )

    return ChatResponse(
        reply=result["reply"],
        detected_language=detected_lang,
        related_questions=result.get("related_questions", []),
        show_escalation=result.get("show_escalation", False),
        was_fallback=result["was_fallback"],
        confidence_score=result.get("confidence_score"),
        tokens_used=result.get("tokens_used", 0),
        metadata=result.get("metadata"),
    )


if __name__ == "__main__":
    uvicorn.run("main:app", host="0.0.0.0", port=8001, reload=True)