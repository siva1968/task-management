<?php

namespace App\Exceptions;

/**
 * Exception thrown when AI input validation fails
 */
class AIValidationException extends AIException
{
    public function __construct(
        string $message,
        string $userMessage = 'Invalid input provided. Please check your data and try again.',
        int $code = 422,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $userMessage, null, $code, $previous);
    }

    public function isRetryable(): bool
    {
        return false; // Validation errors require user correction
    }
}
