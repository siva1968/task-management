<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.openai.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', '');
        $this->model = config('services.openai.model', 'gpt-3.5-turbo');
    }

    public function complete(string $prompt, array $options = []): string
    {
        if (!$this->isAvailable()) {
            throw new \Exception('OpenAI API key not configured');
        }

        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 1000;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ]);

            if ($response->failed()) {
                Log::error('OpenAI API error', ['response' => $response->body()]);
                throw new \Exception('OpenAI API request failed: ' . $response->body());
            }

            return $response->json('choices.0.message.content', '');
        } catch (\Exception $e) {
            Log::error('OpenAI completion error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function completeJson(string $prompt, array $schema = [], array $options = []): array
    {
        $jsonPrompt = $prompt . "\n\nRespond with valid JSON only, no additional text.";

        $response = $this->complete($jsonPrompt, array_merge($options, ['temperature' => 0.3]));

        // Extract JSON from response (in case there's surrounding text)
        $jsonMatch = preg_match('/\{[\s\S]*\}|\[[\s\S]*\]/', $response, $matches);

        if ($jsonMatch) {
            $jsonString = $matches[0];
        } else {
            $jsonString = $response;
        }

        try {
            $decoded = json_decode($jsonString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Failed to decode JSON: ' . json_last_error_msg());
            }
            return $decoded ?? [];
        } catch (\Exception $e) {
            Log::error('JSON decode error', ['response' => $response, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    public function getName(): string
    {
        return 'OpenAI';
    }
}
