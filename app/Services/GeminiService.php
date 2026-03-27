<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function improveOrGenerate(string $prompt, int $maxTokens = 400): string
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        if (blank($apiKey)) {
            throw new \RuntimeException('Gemini API key not configured');
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'maxOutputTokens' => $maxTokens,
                        'temperature' => 0.6,
                    ],
                ])
                ->throw(); // Throws RequestException on 4xx/5xx
        } catch (RequestException|ConnectionException $e) {
            Log::error('Gemini API request failed', [
                'prompt' => $prompt,
                'status' => $e->response?->status(),
                'body' => $e->response?->json(),
            ]);
            throw new \RuntimeException('Gemini API error: ', ($e->response?->json()['error']['message']));
        }

        Log::info('Gemini API Response: ', [
            'prompt' => $prompt,
            'response' => $response->json() ?? null,
        ]);

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

        if (blank($text)) {
            $finishReason = data_get($response->json(), 'candidates.0.finishReason');
            if ($finishReason === 'SAFETY') {
                throw new \RuntimeException('Content blocked by Gemini safety settings.');
            }
            throw new \RuntimeException('Gemini returned an empty response.');
        }

        return $text;
    }
}
