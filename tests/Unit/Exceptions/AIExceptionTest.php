<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\AIException;
use App\Exceptions\AIProviderException;
use App\Exceptions\AIRateLimitException;
use App\Exceptions\AIResponseException;
use App\Exceptions\AIValidationException;
use PHPUnit\Framework\TestCase;

class AIExceptionTest extends TestCase
{
    /** @test */
    public function ai_exception_has_user_message()
    {
        $exception = new AIException(
            'Technical error message',
            'User-friendly message'
        );

        $this->assertEquals('Technical error message', $exception->getMessage());
        $this->assertEquals('User-friendly message', $exception->getUserMessage());
    }

    /** @test */
    public function ai_exception_tracks_provider()
    {
        $exception = new AIException(
            'Error message',
            'User message',
            'openai'
        );

        $this->assertEquals('openai', $exception->getProvider());
    }

    /** @test */
    public function ai_exception_is_not_retryable_by_default()
    {
        $exception = new AIException('Error');

        $this->assertFalse($exception->isRetryable());
    }

    /** @test */
    public function ai_provider_exception_generates_user_message()
    {
        $exception = new AIProviderException(
            'Connection failed',
            'openai',
            500
        );

        $this->assertStringContainsString('openai', $exception->getUserMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    /** @test */
    public function ai_provider_exception_is_retryable_for_5xx_errors()
    {
        $retriable = [500, 502, 503, 504];

        foreach ($retriable as $code) {
            $exception = new AIProviderException('Error', 'openai', $code);
            $this->assertTrue($exception->isRetryable(), "Code {$code} should be retryable");
        }
    }

    /** @test */
    public function ai_provider_exception_is_retryable_for_429_and_408()
    {
        $exception429 = new AIProviderException('Rate limit', 'openai', 429);
        $exception408 = new AIProviderException('Timeout', 'openai', 408);

        $this->assertTrue($exception429->isRetryable());
        $this->assertTrue($exception408->isRetryable());
    }

    /** @test */
    public function ai_provider_exception_is_not_retryable_for_4xx_errors()
    {
        $nonRetriable = [400, 401, 403, 404];

        foreach ($nonRetriable as $code) {
            $exception = new AIProviderException('Error', 'openai', $code);
            $this->assertFalse($exception->isRetryable(), "Code {$code} should not be retryable");
        }
    }

    /** @test */
    public function ai_validation_exception_has_422_code()
    {
        $exception = new AIValidationException('Invalid input');

        $this->assertEquals(422, $exception->getCode());
    }

    /** @test */
    public function ai_validation_exception_is_not_retryable()
    {
        $exception = new AIValidationException('Invalid input');

        $this->assertFalse($exception->isRetryable());
    }

    /** @test */
    public function ai_rate_limit_exception_includes_retry_after()
    {
        $exception = new AIRateLimitException('Rate limit exceeded', 60, 'openai');

        $this->assertEquals(60, $exception->getRetryAfter());
        $this->assertEquals(429, $exception->getCode());
        $this->assertStringContainsString('60 seconds', $exception->getUserMessage());
    }

    /** @test */
    public function ai_rate_limit_exception_is_retryable()
    {
        $exception = new AIRateLimitException('Rate limit exceeded', 60);

        $this->assertTrue($exception->isRetryable());
    }

    /** @test */
    public function ai_response_exception_is_retryable()
    {
        $exception = new AIResponseException('Invalid JSON response');

        $this->assertTrue($exception->isRetryable());
    }

    /** @test */
    public function ai_response_exception_has_500_code_by_default()
    {
        $exception = new AIResponseException('Parse error');

        $this->assertEquals(500, $exception->getCode());
    }
}
