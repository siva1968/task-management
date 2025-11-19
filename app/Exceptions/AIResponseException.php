<?php

namespace App\Exceptions;

/**
 * Exception thrown when AI provider returns invalid or unexpected response
 */
class AIResponseException extends AIException
{
    public function __construct(
        string $message,
        ?string $provider = null,
        int $code = 500,
        ?\Exception $previous = null
    ) {
        $userMessage = 'The AI service returned an unexpected response. Please try again.';

        parent::__construct($message, $userMessage, $provider, $code, $previous);
    }

    public function isRetryable(): bool
    {
        return true; // Transient parsing/format errors might be retryable
    }
}
