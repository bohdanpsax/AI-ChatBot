<?php

namespace App\Http\Controllers;

use App\Models\Campsite;
use App\Models\Conversation;
use App\Models\Guest;
use App\Models\Message;
use App\Services\AiProxyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function __construct(private AiProxyService $aiProxy) {}

    public function sendMessage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'campsite_id' => 'required|exists:campsites,id',
            'message'     => 'required|string|max:2000',
            'session_id'  => 'nullable|string',
            'guest_id'    => 'nullable|exists:guests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $campsite = Campsite::findOrFail($request->campsite_id);

        $conversation = $this->resolveConversation(
            $campsite,
            $request->session_id,
            $request->guest_id
        );

        Message::create([
            'conversation_id' => $conversation->id,
            'role'            => 'user',
            'content'         => $request->message,
        ]);

        $conversation->incrementMessageCount();

        $context = $this->buildContext($campsite, $conversation);

        $aiResponse = $this->aiProxy->chat(
            message: $request->message,
            context: $context,
            history: $conversation->getRecentMessagesForAI(10),
            sessionId: $conversation->session_id,
        );

        if (!empty($aiResponse['detected_language'])) {
            $conversation->update(['detected_language' => $aiResponse['detected_language']]);
        }

        Message::create([
            'conversation_id'  => $conversation->id,
            'role'             => 'assistant',
            'content'          => $aiResponse['reply'],
            'tokens_used'      => $aiResponse['tokens_used'] ?? 0,
            'confidence_score' => $aiResponse['confidence_score'] ?? null,
            'was_fallback'     => $aiResponse['was_fallback'] ?? false,
            'metadata'         => $aiResponse['metadata'] ?? null,
        ]);

        $conversation->incrementMessageCount();

        return response()->json([
            'session_id'        => $conversation->session_id,
            'reply'             => $aiResponse['reply'],
            'detected_language' => $aiResponse['detected_language'] ?? 'en',
            'related_questions' => $aiResponse['related_questions'] ?? [],
            'show_escalation'   => $aiResponse['show_escalation'] ?? false,
            'was_fallback'      => $aiResponse['was_fallback'] ?? false,
            'confidence_score'  => $aiResponse['confidence_score'] ?? null,
        ]);
    }

    public function getHistory(string $sessionId): JsonResponse
    {
        $conversation = Conversation::where('session_id', $sessionId)
            ->with('messages')
            ->firstOrFail();

        return response()->json([
            'session_id' => $conversation->session_id,
            'status'     => $conversation->status,
            'language'   => $conversation->detected_language,
            'messages'   => $conversation->messages->map(fn(Message $m) => [
                'role'       => $m->role,
                'content'    => $m->content,
                'created_at' => $m->created_at->toISOString(),
            ]),
        ]);
    }

    private function resolveConversation(Campsite $campsite, ?string $sessionId, ?int $guestId): Conversation
    {
        if ($sessionId) {
            $conversation = Conversation::where('session_id', $sessionId)
                ->where('campsite_id', $campsite->id)
                ->first();

            if ($conversation) {
                return $conversation;
            }
        }

        return Conversation::create([
            'campsite_id' => $campsite->id,
            'guest_id'    => $guestId,
        ]);
    }

    private function buildContext(Campsite $campsite, Conversation $conversation): array
    {
        $language = $conversation->detected_language ?? 'en';
        $faqs = $campsite->activeFaqsForLanguage($language);

        return [
            'campsite' => [
                'name'          => $campsite->name,
                'description'   => $campsite->description,
                'location'      => $campsite->location,
                'phone'         => $campsite->phone,
                'email'         => $campsite->email,
                'checkin_time'  => $campsite->checkin_time,
                'checkout_time' => $campsite->checkout_time,
            ],
            'faqs' => $faqs->map(fn(mixed $faq) => [
                'category' => $faq->category,
                'question' => $faq->question,
                'answer'   => $faq->answer,
            ])->toArray(),
        ];
    }
}