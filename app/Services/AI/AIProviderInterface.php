<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * Generate a completion from the AI provider.
     *
     * @param string $prompt
     * @param array $options
     * @return string
     */
    public function complete(string $prompt, array $options = []): string;

    /**
     * Generate a structured JSON response from the AI provider.
     *
     * @param string $prompt
     * @param array $schema
     * @param array $options
     * @return array
     */
    public function completeJson(string $prompt, array $schema = [], array $options = []): array;

    /**
     * Check if the provider is available (has API key configured).
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Get the provider name.
     *
     * @return string
     */
    public function getName(): string;
}
