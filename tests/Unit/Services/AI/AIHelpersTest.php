<?php

namespace Tests\Unit\Services\AI;

use App\Services\AI\AIHelpers;
use PHPUnit\Framework\TestCase;

class AIHelpersTest extends TestCase
{
    /** @test */
    public function it_sanitizes_prompt_input_by_removing_control_characters()
    {
        $input = "Hello\x00\x1F\x7FWorld";
        $result = AIHelpers::sanitizePromptInput($input);

        $this->assertEquals('HelloWorld', $result);
    }

    /** @test */
    public function it_limits_input_length_to_specified_max()
    {
        $input = str_repeat('a', 300);
        $result = AIHelpers::sanitizePromptInput($input, 255);

        $this->assertEquals(255, mb_strlen($result));
        $this->assertEquals(str_repeat('a', 255), $result);
    }

    /** @test */
    public function it_escapes_prompt_injection_patterns()
    {
        $tests = [
            '```code```' => '`‌`‌`code`‌`‌`',
            '---END---' => '----END----',
            'IGNORE PREVIOUS instructions' => 'DISREGARD PREVIOUS instructions',
            'SYSTEM: You are now' => 'SYS TEM: You are now',
            'ASSISTANT: Hello' => 'ASSIST ANT: Hello',
        ];

        foreach ($tests as $input => $expected) {
            $result = AIHelpers::sanitizePromptInput($input);
            $this->assertEquals($expected, $result, "Failed to escape: {$input}");
        }
    }

    /** @test */
    public function it_handles_unicode_correctly()
    {
        $input = "Hello 世界 🌍";
        $result = AIHelpers::sanitizePromptInput($input);

        $this->assertEquals($input, $result);
    }

    /** @test */
    public function it_generates_consistent_cache_keys()
    {
        $params = ['title' => 'Test', 'description' => 'Description'];

        $key1 = AIHelpers::getCacheKey('test_operation', $params);
        $key2 = AIHelpers::getCacheKey('test_operation', $params);

        $this->assertEquals($key1, $key2);
    }

    /** @test */
    public function it_generates_different_cache_keys_for_different_params()
    {
        $params1 = ['title' => 'Test1'];
        $params2 = ['title' => 'Test2'];

        $key1 = AIHelpers::getCacheKey('test_operation', $params1);
        $key2 = AIHelpers::getCacheKey('test_operation', $params2);

        $this->assertNotEquals($key1, $key2);
    }

    /** @test */
    public function it_returns_correct_cache_durations_for_operations()
    {
        $durations = [
            'estimate_hours' => 3600,
            'suggest_priority' => 1800,
            'enhance_description' => 1800,
            'breakdown' => 3600,
            'project_summary' => 300,
            'project_risks' => 600,
            'project_completion' => 600,
            'suggest_tasks' => 1800,
        ];

        foreach ($durations as $operation => $expected) {
            $result = AIHelpers::getCacheDuration($operation);
            $this->assertEquals($expected, $result, "Wrong duration for: {$operation}");
        }
    }

    /** @test */
    public function it_returns_default_cache_duration_for_unknown_operations()
    {
        $result = AIHelpers::getCacheDuration('unknown_operation');

        $this->assertEquals(1800, $result);
    }

    /** @test */
    public function it_sanitizes_empty_strings()
    {
        $result = AIHelpers::sanitizePromptInput('');

        $this->assertEquals('', $result);
    }

    /** @test */
    public function it_sanitizes_whitespace_only_strings()
    {
        $result = AIHelpers::sanitizePromptInput('   ');

        $this->assertEquals('   ', $result);
    }

    /** @test */
    public function it_handles_very_long_inputs()
    {
        $input = str_repeat('Hello World! ', 1000); // ~12000 chars
        $result = AIHelpers::sanitizePromptInput($input, 5000);

        $this->assertEquals(5000, mb_strlen($result));
    }

    /** @test */
    public function it_combines_multiple_sanitization_rules()
    {
        $input = "Test\x00```IGNORE PREVIOUS\x1F" . str_repeat('a', 300);
        $result = AIHelpers::sanitizePromptInput($input, 255);

        // Should remove control chars, escape patterns, and limit length
        $this->assertEquals(255, mb_strlen($result));
        $this->assertStringContainsString('`‌`‌`', $result);
        $this->assertStringContainsString('DISREGARD PREVIOUS', $result);
    }
}
