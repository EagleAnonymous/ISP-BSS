<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
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

        try {
            $response = Http::withOptions([
                'verify' => ! app()->isLocal(),
            ])
                ->withToken($this->apiKey)
                ->acceptJson()
                ->timeout(30)
                ->connectTimeout(10)
                ->retry(2, 300)
                ->post($this->baseUrl.'/chat/completions', [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => $temperature,
                    'max_tokens' => $maxTokens,
                ]);
        } catch (ConnectionException $e) {
            report($e);

            throw new RuntimeException(
                'Could not connect to the AI service. Please check your network connection and try again.',
            );
        }

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

        if (! is_array($data) || ! isset($data['choices'][0]['message']['content'])) {
            Log::error('Groq API unexpected response structure', [
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Groq API returned an unexpected response format.');
        }

        return trim($data['choices'][0]['message']['content'] ?? '');
    }
}
