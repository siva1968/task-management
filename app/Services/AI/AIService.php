<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Log;

class AIService
{
    protected AIProviderInterface $provider;
    protected array $availableProviders = [];

    public function __construct()
    {
        $this->availableProviders = [
            'openai' => new OpenAIProvider(),
            'anthropic' => new AnthropicProvider(),
        ];

        $this->setDefaultProvider();
    }

    /**
     * Set the default provider based on configuration or availability.
     */
    protected function setDefaultProvider(): void
    {
        $preferredProvider = config('services.ai.default_provider', 'openai');

        // Use preferred provider if available
        if (isset($this->availableProviders[$preferredProvider]) &&
            $this->availableProviders[$preferredProvider]->isAvailable()) {
            $this->provider = $this->availableProviders[$preferredProvider];
            return;
        }

        // Fall back to first available provider
        foreach ($this->availableProviders as $provider) {
            if ($provider->isAvailable()) {
                $this->provider = $provider;
                return;
            }
        }

        throw new \Exception('No AI provider configured. Please set API keys in .env');
    }

    /**
     * Set a specific provider.
     */
    public function setProvider(string $providerName): self
    {
        if (!isset($this->availableProviders[$providerName])) {
            throw new \Exception("Unknown AI provider: {$providerName}");
        }

        if (!$this->availableProviders[$providerName]->isAvailable()) {
            throw new \Exception("AI provider {$providerName} is not configured");
        }

        $this->provider = $this->availableProviders[$providerName];
        return $this;
    }

    /**
     * Get the current provider.
     */
    public function getProvider(): AIProviderInterface
    {
        return $this->provider;
    }

    /**
     * Get all available providers.
     */
    public function getAvailableProviders(): array
    {
        return array_filter($this->availableProviders, fn($p) => $p->isAvailable());
    }

    /**
     * Generate a completion.
     */
    public function complete(string $prompt, array $options = []): string
    {
        return $this->provider->complete($prompt, $options);
    }

    /**
     * Generate a JSON completion.
     */
    public function completeJson(string $prompt, array $schema = [], array $options = []): array
    {
        return $this->provider->completeJson($prompt, $schema, $options);
    }

    /**
     * Check if AI is available.
     */
    public function isAvailable(): bool
    {
        return count($this->getAvailableProviders()) > 0;
    }
}
