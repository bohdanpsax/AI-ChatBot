<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiProxyService
{
    private string $baseUrl;
    private int $timeoutSeconds = 30;

    public function __construct()
    {
        $this->baseUrl = config('services.ai_service.url', 'http://localhost:8001');
    }

    public function chat(
        string $message,
        array  $context,
        array  $history = [],
        string $sessionId = '',
    ): array {
        try {
            $response = Http::timeout($this->timeoutSeconds)
                ->post("{$this->baseUrl}/chat", [
                    'message'    => $message,
                    'context'    => $context,
                    'history'    => $history,
                    'session_id' => $sessionId,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('AI service error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return $this->fallbackResponse();

        } catch (\Exception $e) {
            Log::error('AI service connection failed', ['error' => $e->getMessage()]);
            return $this->fallbackResponse();
        }
    }

    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }

    private function fallbackResponse(): array
    {
        return [
            'reply'             => 'Sorry, the assistant is temporarily unavailable. Please contact reception directly.',
            'detected_language' => 'en',
            'was_fallback'      => true,
            'confidence_score'  => 0.0,
            'tokens_used'       => 0,
            'metadata'          => ['reason' => 'ai_service_unavailable'],
        ];
    }
}