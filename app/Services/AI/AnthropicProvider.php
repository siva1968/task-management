<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicProvider implements AIProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://api.anthropic.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', '');
        $this->model = config('services.anthropic.model', 'claude-3-5-sonnet-20241022');
    }

    public function complete(string $prompt, array $options = []): string
    {
        if (!$this->isAvailable()) {
            throw new \Exception('Anthropic API key not configured');
        }

        $temperature = $options['temperature'] ?? 0.7;
        $maxTokens = $options['max_tokens'] ?? 1000;

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->baseUrl . '/messages', [
                'model' => $this->model,
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
            ]);

            if ($response->failed()) {
                Log::error('Anthropic API error', ['response' => $response->body()]);
                throw new \Exception('Anthropic API request failed: ' . $response->body());
            }

            $content = $response->json('content.0.text', '');
            return $content;
        } catch (\Exception $e) {
            Log::error('Anthropic completion error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function completeJson(string $prompt, array $schema = [], array $options = []): array
    {
        $jsonPrompt = $prompt . "\n\nRespond with valid JSON only, no additional text or markdown formatting.";

        $response = $this->complete($jsonPrompt, array_merge($options, ['temperature' => 0.3]));

        // Extract JSON from response (in case there's surrounding text or markdown)
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
        return 'Anthropic Claude';
    }
}
