<?php

namespace App\Exceptions;

use Exception;

/**
 * Base exception for all AI-related errors
 */
class AIException extends Exception
{
    protected string $userMessage;
    protected ?string $provider;

    public function __construct(
        string $message,
        string $userMessage = 'An AI operation failed. Please try again.',
        ?string $provider = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->userMessage = $userMessage;
        $this->provider = $provider;
    }

    /**
     * Get user-friendly error message
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Get AI provider name
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }

    /**
     * Check if error is retryable
     */
    public function isRetryable(): bool
    {
        return false;
    }
}
