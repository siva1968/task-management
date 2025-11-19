<?php

namespace App\Services\AI;

use App\Models\Project;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ProjectAIService
{
    protected AIService $ai;

    public function __construct(AIService $ai)
    {
        $this->ai = $ai;
    }

    /**
     * Generate a project summary.
     */
    public function generateSummary(Project $project, string $period = 'weekly'): string
    {
        $project->load(['tasks', 'owner']);

        $totalTasks = $project->tasks->count();
        $completedTasks = $project->tasks->where('status', 'completed')->count();
        $inProgressTasks = $project->tasks->where('status', 'in_progress')->count();
        $overdueTasks = $project->tasks->filter->isOverdue()->count();

        // Get recent activity based on period
        $since = match($period) {
            'daily' => Carbon::now()->subDay(),
            'weekly' => Carbon::now()->subWeek(),
            'monthly' => Carbon::now()->subMonth(),
            default => Carbon::now()->subWeek(),
        };

        $recentlyCompleted = $project->tasks()
            ->where('status', 'completed')
            ->where('completed_at', '>=', $since)
            ->get(['title'])
            ->map(fn($t) => AIHelpers::sanitizePromptInput($t->title, 255))
            ->join(', ');

        // Sanitize project data
        $projectName = AIHelpers::sanitizePromptInput($project->name, 255);
        $ownerName = AIHelpers::sanitizePromptInput($project->owner->name, 255);
        $projectStatus = AIHelpers::sanitizePromptInput($project->status, 50);

        // Check cache
        $cacheKey = AIHelpers::getCacheKey('project_summary', [
            'project_id' => $project->id,
            'period' => $period,
            'metrics' => compact('totalTasks', 'completedTasks', 'inProgressTasks', 'overdueTasks'),
        ]);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = "You are a project manager creating a {$period} status report. Generate a professional summary.\n\n";
        $prompt .= "Project: {$projectName}\n";
        $prompt .= "Owner: {$ownerName}\n";
        $prompt .= "Status: {$projectStatus}\n\n";

        $prompt .= "Metrics:\n";
        $prompt .= "- Total Tasks: {$totalTasks}\n";
        $prompt .= "- Completed: {$completedTasks}\n";
        $prompt .= "- In Progress: {$inProgressTasks}\n";
        $prompt .= "- Overdue: {$overdueTasks}\n";
        $prompt .= "- Completion Rate: " . ($totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0) . "%\n\n";

        if ($recentlyCompleted) {
            $prompt .= "Recently Completed Tasks: {$recentlyCompleted}\n\n";
        }

        $prompt .= "Generate a concise summary (200-300 words) covering:\n";
        $prompt .= "1. Overall progress and health\n";
        $prompt .= "2. Key accomplishments\n";
        $prompt .= "3. Concerns or blockers (if any)\n";
        $prompt .= "4. Recommended next steps\n\n";
        $prompt .= "Format with markdown headings and bullet points.";

        $summary = $this->ai->complete($prompt, ['max_tokens' => 800]);

        // Cache the result
        Cache::put($cacheKey, $summary, AIHelpers::getCacheDuration('project_summary'));

        return $summary;
    }

    /**
     * Analyze project risks.
     */
    public function analyzeRisks(Project $project): array
    {
        $project->load(['tasks']);

        $overdueTasks = $project->tasks->filter->isOverdue()->count();
        $urgentTasks = $project->tasks->where('priority', 'urgent')->count();
        $unassignedTasks = $project->tasks()
            ->whereDoesntHave('assignedUsers')
            ->count();

        $totalTasks = $project->tasks->count();
        $daysUntilDeadline = $project->end_date ? Carbon::now()->diffInDays($project->end_date, false) : null;

        // Sanitize project data
        $projectName = AIHelpers::sanitizePromptInput($project->name, 255);

        // Check cache
        $cacheKey = AIHelpers::getCacheKey('project_risks', [
            'project_id' => $project->id,
            'metrics' => compact('totalTasks', 'overdueTasks', 'urgentTasks', 'unassignedTasks', 'daysUntilDeadline'),
        ]);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = "You are a project risk analyst. Analyze potential risks for this project.\n\n";
        $prompt .= "Project: {$projectName}\n";
        $prompt .= "Total Tasks: {$totalTasks}\n";
        $prompt .= "Overdue Tasks: {$overdueTasks}\n";
        $prompt .= "Urgent Tasks: {$urgentTasks}\n";
        $prompt .= "Unassigned Tasks: {$unassignedTasks}\n";

        if ($daysUntilDeadline !== null) {
            $prompt .= "Days Until Deadline: {$daysUntilDeadline}\n";
        }

        $prompt .= "\nIdentify 3-5 key risks and provide mitigation strategies. Respond with JSON:\n";
        $prompt .= json_encode([
            'risks' => [
                [
                    'title' => 'Risk title',
                    'severity' => 'high',
                    'description' => 'Risk description',
                    'mitigation' => 'How to address it'
                ],
            ]
        ], JSON_PRETTY_PRINT);

        $result = $this->ai->completeJson($prompt);

        $risks = $result['risks'] ?? [];

        // Cache the result
        Cache::put($cacheKey, $risks, AIHelpers::getCacheDuration('project_risks'));

        return $risks;
    }

    /**
     * Predict project completion date.
     */
    public function predictCompletion(Project $project): array
    {
        $project->load(['tasks']);

        $totalTasks = $project->tasks->count();
        $completedTasks = $project->tasks->where('status', 'completed')->count();
        $remainingTasks = $totalTasks - $completedTasks;

        // Calculate average completion rate
        $completedWithDates = $project->tasks()
            ->whereNotNull('completed_at')
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();

        $avgHoursPerTask = $completedWithDates->avg('actual_hours') ?? 8;
        $plannedEndDate = $project->end_date ? $project->end_date->format('Y-m-d') : null;

        // Sanitize project data
        $projectName = AIHelpers::sanitizePromptInput($project->name, 255);

        // Check cache
        $cacheKey = AIHelpers::getCacheKey('project_completion', [
            'project_id' => $project->id,
            'metrics' => compact('totalTasks', 'completedTasks', 'remainingTasks', 'avgHoursPerTask', 'plannedEndDate'),
        ]);
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $prompt = "You are a project timeline analyst. Predict the project completion date.\n\n";
        $prompt .= "Project: {$projectName}\n";
        $prompt .= "Total Tasks: {$totalTasks}\n";
        $prompt .= "Completed: {$completedTasks}\n";
        $prompt .= "Remaining: {$remainingTasks}\n";
        $prompt .= "Average Hours per Task: " . round($avgHoursPerTask, 1) . "\n";

        if ($plannedEndDate) {
            $prompt .= "Planned End Date: {$plannedEndDate}\n";
        }

        $prompt .= "\nPredict completion with JSON:\n";
        $prompt .= json_encode([
            'predicted_date' => '2025-12-15',
            'confidence' => 'medium',
            'on_track' => true,
            'analysis' => 'Brief analysis'
        ], JSON_PRETTY_PRINT);

        $result = $this->ai->completeJson($prompt);

        $prediction = $result ?? [];

        // Cache the result
        Cache::put($cacheKey, $prediction, AIHelpers::getCacheDuration('project_completion'));

        return $prediction;
    }
}
