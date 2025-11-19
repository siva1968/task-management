<?php

namespace App\Exceptions;

/**
 * Exception thrown when AI provider API fails
 */
class AIProviderException extends AIException
{
    public function __construct(
        string $message,
        ?string $provider = null,
        int $code = 0,
        ?\Exception $previous = null
    ) {
        $userMessage = $provider
            ? "The {$provider} AI service is currently unavailable. Please try again later."
            : 'The AI service is currently unavailable. Please try again later.';

        parent::__construct($message, $userMessage, $provider, $code, $previous);
    }

    public function isRetryable(): bool
    {
        // Network errors, timeouts, and 5xx errors are retryable
        return in_array($this->code, [408, 429, 500, 502, 503, 504]);
    }
}
