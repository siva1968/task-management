<?php

namespace App\Services\AI;

class AIHelpers
{
    /**
     * Sanitize user input for AI prompts to prevent injection attacks.
     *
     * @param string $input
     * @param int $maxLength
     * @return string
     */
    public static function sanitizePromptInput(string $input, int $maxLength = 5000): string
    {
        // Remove any control characters
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', $input);

        // Trim whitespace
        $sanitized = trim($sanitized);

        // Limit length to prevent abuse
        if (mb_strlen($sanitized) > $maxLength) {
            $sanitized = mb_substr($sanitized, 0, $maxLength);
        }

        // Escape potential prompt injection patterns
        $sanitized = str_replace(
            ['```', '---END---', 'IGNORE PREVIOUS', 'SYSTEM:', 'ASSISTANT:'],
            ['`‌`‌`', '----END----', 'DISREGARD PREVIOUS', 'SYS TEM:', 'ASSIST ANT:'],
            $sanitized
        );

        return $sanitized;
    }

    /**
     * Build a safe prompt with sanitized inputs.
     *
     * @param string $template
     * @param array $variables
     * @return string
     */
    public static function buildSafePrompt(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            if (is_string($value)) {
                $variables[$key] = self::sanitizePromptInput($value);
            }
        }

        return str_replace(
            array_map(fn($key) => "{{$key}}", array_keys($variables)),
            array_values($variables),
            $template
        );
    }

    /**
     * Generate a cache key for AI requests.
     *
     * @param string $operation
     * @param array $params
     * @return string
     */
    public static function getCacheKey(string $operation, array $params): string
    {
        // Sort params for consistent cache keys
        ksort($params);

        // Generate hash
        $hash = md5(json_encode($params));

        return "ai_{$operation}_{$hash}";
    }

    /**
     * Get cache duration in seconds based on operation type.
     *
     * @param string $operation
     * @return int
     */
    public static function getCacheDuration(string $operation): int
    {
        return match($operation) {
            'estimate_hours' => 3600, // 1 hour
            'suggest_priority' => 1800, // 30 minutes
            'enhance_description' => 1800, // 30 minutes
            'breakdown' => 3600, // 1 hour
            'project_summary' => 300, // 5 minutes (changes frequently)
            'project_risks' => 600, // 10 minutes
            'project_completion' => 600, // 10 minutes
            default => 1800, // 30 minutes default
        };
    }
}
