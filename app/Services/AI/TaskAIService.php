<?php

namespace App\Services\AI;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class TaskAIService
{
    protected AIService $ai;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Break down a task into subtasks.
     */
    public function breakdownTask(string $title, ?string $description = null): array
    {
        // Sanitize inputs
        $title = AIHelpers::sanitizePromptInput($title, 255);
        $description = $description ? AIHelpers::sanitizePromptInput($description, 2000) : null;

        // Check cache
        $cacheKey = AIHelpers::getCacheKey('breakdown', compact('title', 'description'));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = "You are a project management assistant. Break down the following task into 3-7 specific, actionable subtasks.\n\n";
        $prompt .= "Task Title: {$title}\n";

        if ($description) {
            $prompt .= "Description: {$description}\n";
        }

        $prompt .= "\nProvide subtasks as a JSON array with this structure:\n";
        $prompt .= json_encode([
            'subtasks' => [
                ['title' => 'Subtask title', 'description' => 'Brief description'],
            ]
        ], JSON_PRETTY_PRINT);

        $result = $this->ai->completeJson($prompt);
        $subtasks = $result['subtasks'] ?? [];

        // Cache the result
        Cache::put($cacheKey, $subtasks, AIHelpers::getCacheDuration('breakdown'));

        return $subtasks;
    }

    /**
     * Estimate hours required for a task.
     */
    public function estimateHours(string $title, ?string $description = null, ?string $priority = null): float
    {
        // Sanitize inputs
        $title = AIHelpers::sanitizePromptInput($title, 255);
        $description = $description ? AIHelpers::sanitizePromptInput($description, 2000) : null;
        $priority = $priority ? AIHelpers::sanitizePromptInput($priority, 20) : null;

        // Check cache
        $cacheKey = AIHelpers::getCacheKey('estimate_hours', compact('title', 'description', 'priority'));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = "You are a project estimation expert. Estimate the number of hours required to complete this task.\n\n";
        $prompt .= "Task Title: {$title}\n";

        if ($description) {
            $prompt .= "Description: {$description}\n";
        }

        if ($priority) {
            $prompt .= "Priority: {$priority}\n";
        }

        $prompt .= "\nConsider typical software development work. Provide your estimate as a JSON object:\n";
        $prompt .= json_encode([
            'estimated_hours' => 8.5,
            'confidence' => 'medium',
            'reasoning' => 'Brief explanation'
        ], JSON_PRETTY_PRINT);

        $result = $this->ai->completeJson($prompt);
        $hours = (float) ($result['estimated_hours'] ?? 0);

        // Cache the result
        Cache::put($cacheKey, $hours, AIHelpers::getCacheDuration('estimate_hours'));

        return $hours;
    }

    /**
     * Suggest priority for a task.
     */
    public function suggestPriority(string $title, ?string $description = null, ?string $dueDate = null): string
    {
        // Sanitize inputs
        $title = AIHelpers::sanitizePromptInput($title, 255);
        $description = $description ? AIHelpers::sanitizePromptInput($description, 2000) : null;
        $dueDate = $dueDate ? AIHelpers::sanitizePromptInput($dueDate, 50) : null;

        // Check cache
        $cacheKey = AIHelpers::getCacheKey('suggest_priority', compact('title', 'description', 'dueDate'));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = "You are a task prioritization expert. Analyze this task and suggest an appropriate priority level.\n\n";
        $prompt .= "Task Title: {$title}\n";

        if ($description) {
            $prompt .= "Description: {$description}\n";
        }

        if ($dueDate) {
            $prompt .= "Due Date: {$dueDate}\n";
        }

        $prompt .= "\nPriority levels: low, medium, high, urgent\n";
        $prompt .= "Respond with JSON:\n";
        $prompt .= json_encode([
            'priority' => 'high',
            'reasoning' => 'Brief explanation why this priority was chosen'
        ], JSON_PRETTY_PRINT);

        $result = $this->ai->completeJson($prompt);

        $priority = $result['priority'] ?? 'medium';

        // Validate priority
        if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
            $priority = 'medium';
        }

        // Cache the result
        Cache::put($cacheKey, $priority, AIHelpers::getCacheDuration('suggest_priority'));

        return $priority;
    }

    /**
     * Enhance task description with AI.
     */
    public function enhanceDescription(string $title, ?string $description = null): string
    {
        // Sanitize inputs
        $title = AIHelpers::sanitizePromptInput($title, 255);
        $description = $description ? AIHelpers::sanitizePromptInput($description, 2000) : null;

        // Check cache
        $cacheKey = AIHelpers::getCacheKey('enhance_description', compact('title', 'description'));
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = "You are a technical writer. Expand this task into a clear, detailed description that includes:\n";
        $prompt .= "- What needs to be done\n";
        $prompt .= "- Acceptance criteria\n";
        $prompt .= "- Any important considerations\n\n";
        $prompt .= "Task Title: {$title}\n";

        if ($description) {
            $prompt .= "Current Description: {$description}\n";
        }

        $prompt .= "\nProvide an enhanced description in plain text (not JSON), formatted with markdown.";

        $enhanced = $this->ai->complete($prompt, ['max_tokens' => 500]);

        // Cache the result
        Cache::put($cacheKey, $enhanced, AIHelpers::getCacheDuration('enhance_description'));

        return $enhanced;
    }

    /**
     * Generate task suggestions based on project context.
     */
    public function suggestNextTasks(Project $project): array
    {
        $completedTasks = $project->tasks()
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get(['title', 'description'])
            ->map(fn($t) => AIHelpers::sanitizePromptInput($t->title, 255))
            ->join(', ');

        $activeTasks = $project->tasks()
            ->whereIn('status', ['in_progress', 'review'])
            ->get(['title', 'description'])
            ->map(fn($t) => AIHelpers::sanitizePromptInput($t->title, 255))
            ->join(', ');

        // Sanitize project data
        $projectName = AIHelpers::sanitizePromptInput($project->name, 255);
        $projectDescription = AIHelpers::sanitizePromptInput($project->description ?? '', 2000);

        // Check cache
        $cacheKey = AIHelpers::getCacheKey('suggest_tasks', [
            'project_id' => $project->id,
            'completed' => $completedTasks,
            'active' => $activeTasks
        ]);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = "You are a project planning assistant. Based on the project context, suggest 3-5 logical next tasks.\n\n";
        $prompt .= "Project: {$projectName}\n";
        $prompt .= "Description: {$projectDescription}\n\n";

        if ($completedTasks) {
            $prompt .= "Recently Completed: {$completedTasks}\n";
        }

        if ($activeTasks) {
            $prompt .= "Currently Active: {$activeTasks}\n";
        }

        $prompt .= "\nSuggest next tasks as JSON:\n";
        $prompt .= json_encode([
            'suggestions' => [
                ['title' => 'Task title', 'description' => 'Why this task makes sense', 'priority' => 'medium'],
            ]
        ], JSON_PRETTY_PRINT);

        $result = $this->ai->completeJson($prompt);

        $suggestions = $result['suggestions'] ?? [];

        // Cache the result
        Cache::put($cacheKey, $suggestions, AIHelpers::getCacheDuration('suggest_tasks'));

        return $suggestions;
    }
}
