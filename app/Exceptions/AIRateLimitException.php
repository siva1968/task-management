<?php

namespace App\Exceptions;

/**
 * Exception thrown when AI provider rate limit is exceeded
 */
class AIRateLimitException extends AIException
{
    protected int $retryAfter;

    public function __construct(
        string $message,
        int $retryAfter = 60,
        ?string $provider = null,
        ?\Exception $previous = null
    ) {
        $userMessage = $retryAfter > 0
            ? "AI service rate limit exceeded. Please try again in {$retryAfter} seconds."
            : 'AI service rate limit exceeded. Please try again later.';

        parent::__construct($message, $userMessage, $provider, 429, $previous);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    public function isRetryable(): bool
    {
        return true;
    }
}
