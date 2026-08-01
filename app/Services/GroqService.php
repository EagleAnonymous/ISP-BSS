<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin wrapper around Groq's OpenAI-compatible Chat Completions API.
 *
 * Used by the subscriber AI chatbot. No external package is required —
 * Laravel's bundled HTTP client talks directly to the API.
 */
class GroqService
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl,
    ) {}

    /*
     * Send a conversation to Groq and return the assistant's reply text.
     *
     * @param  array  $messages  List of messages, each with a "role" and "content".
     */
    public function chat(array $messages, float $temperature = 0.7, int $maxTokens = 512): string
    {
        if (blank($this->apiKey)) {
            throw new RuntimeException('GROQ_API_KEY is not configured. Add it to your .env file and run php artisan config:clear.');
        }

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout(60)
            ->retry(2, 300)
            ->post($this->baseUrl.'/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();

            Log::error('Groq API request failed', [
                'status' => $status,
                'body' => $body,
            ]);

            throw new RuntimeException(
                'Groq API request failed (HTTP '.$status.').',
                $status,
            );
        }

        $data = $response->json();

        return trim($data['choices'][0]['message']['content'] ?? '');
    }
}

