<?php

namespace App\Services\AI;

use App\Models\Project;
use Illuminate\Support\Carbon;

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
            ->map(fn($t) => $t->title)
            ->join(', ');

        $prompt = "You are a project manager creating a {$period} status report. Generate a professional summary.\n\n";
        $prompt .= "Project: {$project->name}\n";
        $prompt .= "Owner: {$project->owner->name}\n";
        $prompt .= "Status: {$project->status}\n\n";

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

        return $this->ai->complete($prompt, ['max_tokens' => 800]);
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

        $prompt = "You are a project risk analyst. Analyze potential risks for this project.\n\n";
        $prompt .= "Project: {$project->name}\n";
        $prompt .= "Total Tasks: {$project->tasks->count()}\n";
        $prompt .= "Overdue Tasks: {$overdueTasks}\n";
        $prompt .= "Urgent Tasks: {$urgentTasks}\n";
        $prompt .= "Unassigned Tasks: {$unassignedTasks}\n";

        if ($project->end_date) {
            $daysUntilDeadline = Carbon::now()->diffInDays($project->end_date, false);
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

        return $result['risks'] ?? [];
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

        $prompt = "You are a project timeline analyst. Predict the project completion date.\n\n";
        $prompt .= "Project: {$project->name}\n";
        $prompt .= "Total Tasks: {$totalTasks}\n";
        $prompt .= "Completed: {$completedTasks}\n";
        $prompt .= "Remaining: {$remainingTasks}\n";
        $prompt .= "Average Hours per Task: " . round($avgHoursPerTask, 1) . "\n";

        if ($project->end_date) {
            $prompt .= "Planned End Date: {$project->end_date->format('Y-m-d')}\n";
        }

        $prompt .= "\nPredict completion with JSON:\n";
        $prompt .= json_encode([
            'predicted_date' => '2025-12-15',
            'confidence' => 'medium',
            'on_track' => true,
            'analysis' => 'Brief analysis'
        ], JSON_PRETTY_PRINT);

        $result = $this->ai->completeJson($prompt);

        return $result ?? [];
    }
}
