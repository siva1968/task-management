<?php

namespace App\Http\Controllers;

use App\Exceptions\AIException;
use App\Exceptions\AIRateLimitException;
use App\Exceptions\AIValidationException;
use App\Models\Project;
use App\Models\Task;
use App\Services\AI\AIService;
use App\Services\AI\ProjectAIService;
use App\Services\AI\TaskAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AIController extends Controller
{
    protected AIService $ai;
    protected TaskAIService $taskAI;
    protected ProjectAIService $projectAI;

    public function __construct(AIService $ai, TaskAIService $taskAI, ProjectAIService $projectAI)
    {
        $this->middleware('auth');
        $this->ai = $ai;
        $this->taskAI = $taskAI;
        $this->projectAI = $projectAI;
    }

    /**
     * Handle AI exceptions and return appropriate responses
     */
    protected function handleAIException(\Exception $e, string $operation, array $context = [])
    {
        // Build log context
        $logContext = array_merge([
            'operation' => $operation,
            'user_id' => auth()->id(),
            'error_type' => get_class($e),
            'error_message' => $e->getMessage(),
        ], $context);

        if ($e instanceof AIException) {
            // Log with appropriate level based on exception type
            if ($e instanceof AIRateLimitException) {
                Log::warning("AI rate limit exceeded: {$operation}", $logContext);
                return response()->json([
                    'success' => false,
                    'error' => $e->getUserMessage(),
                    'retry_after' => $e->getRetryAfter(),
                ], 429);
            }

            if ($e instanceof AIValidationException) {
                Log::info("AI validation error: {$operation}", $logContext);
                return response()->json([
                    'success' => false,
                    'error' => $e->getUserMessage(),
                ], 422);
            }

            // Other AI exceptions
            Log::error("AI operation failed: {$operation}", array_merge($logContext, [
                'provider' => $e->getProvider(),
                'retryable' => $e->isRetryable(),
            ]));

            return response()->json([
                'success' => false,
                'error' => $e->getUserMessage(),
                'retryable' => $e->isRetryable(),
            ], $e->getCode() ?: 500);
        }

        // Generic exception - log as error
        Log::error("Unexpected error in AI operation: {$operation}", $logContext);

        return response()->json([
            'success' => false,
            'error' => 'An unexpected error occurred. Please try again later.',
        ], 500);
    }

    /**
     * Break down a task into subtasks.
     */
    public function breakdownTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        try {
            $subtasks = $this->taskAI->breakdownTask(
                $request->title,
                $request->description
            );

            Log::info('AI task breakdown successful', [
                'user_id' => auth()->id(),
                'subtasks_count' => count($subtasks),
            ]);

            return response()->json([
                'success' => true,
                'subtasks' => $subtasks,
            ]);
        } catch (\Exception $e) {
            return $this->handleAIException($e, 'breakdown_task', [
                'title_length' => strlen($request->title),
                'has_description' => !empty($request->description),
            ]);
        }
    }

    /**
     * Estimate hours for a task.
     */
    public function estimateHours(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        try {
            $hours = $this->taskAI->estimateHours(
                $request->title,
                $request->description,
                $request->priority
            );

            Log::info('AI hours estimation successful', [
                'user_id' => auth()->id(),
                'estimated_hours' => $hours,
                'priority' => $request->priority,
            ]);

            return response()->json([
                'success' => true,
                'estimated_hours' => $hours,
            ]);
        } catch (\Exception $e) {
            return $this->handleAIException($e, 'estimate_hours', [
                'priority' => $request->priority,
            ]);
        }
    }

    /**
     * Suggest priority for a task.
     */
    public function suggestPriority(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'nullable|date',
        ]);

        try {
            $priority = $this->taskAI->suggestPriority(
                $request->title,
                $request->description,
                $request->due_date
            );

            Log::info('AI priority suggestion successful', [
                'user_id' => auth()->id(),
                'suggested_priority' => $priority,
                'has_due_date' => !empty($request->due_date),
            ]);

            return response()->json([
                'success' => true,
                'priority' => $priority,
            ]);
        } catch (\Exception $e) {
            return $this->handleAIException($e, 'suggest_priority', [
                'has_due_date' => !empty($request->due_date),
            ]);
        }
    }

    /**
     * Enhance task description.
     */
    public function enhanceDescription(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        try {
            $enhanced = $this->taskAI->enhanceDescription(
                $request->title,
                $request->description
            );

            Log::info('AI description enhancement successful', [
                'user_id' => auth()->id(),
                'original_length' => strlen($request->description ?? ''),
                'enhanced_length' => strlen($enhanced),
            ]);

            return response()->json([
                'success' => true,
                'description' => $enhanced,
            ]);
        } catch (\Exception $e) {
            return $this->handleAIException($e, 'enhance_description');
        }
    }

    /**
     * Generate project summary.
     */
    public function projectSummary(Project $project, Request $request)
    {
        $this->authorize('view', $project);

        $request->validate([
            'period' => 'nullable|in:daily,weekly,monthly',
        ]);

        try {
            $summary = $this->projectAI->generateSummary(
                $project,
                $request->get('period', 'weekly')
            );

            Log::info('AI project summary generated', [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'period' => $request->get('period', 'weekly'),
            ]);

            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return $this->handleAIException($e, 'project_summary', [
                'project_id' => $project->id,
                'period' => $request->get('period', 'weekly'),
            ]);
        }
    }

    /**
     * Analyze project risks.
     */
    public function projectRisks(Project $project)
    {
        $this->authorize('view', $project);

        try {
            $risks = $this->projectAI->analyzeRisks($project);

            Log::info('AI risk analysis completed', [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'risks_count' => count($risks),
            ]);

            return response()->json([
                'success' => true,
                'risks' => $risks,
            ]);
        } catch (\Exception $e) {
            return $this->handleAIException($e, 'project_risks', [
                'project_id' => $project->id,
            ]);
        }
    }

    /**
     * Predict project completion.
     */
    public function projectCompletion(Project $project)
    {
        $this->authorize('view', $project);

        try {
            $prediction = $this->projectAI->predictCompletion($project);

            Log::info('AI completion prediction generated', [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'predicted_date' => $prediction['predicted_date'] ?? null,
                'on_track' => $prediction['on_track'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'prediction' => $prediction,
            ]);
        } catch (\Exception $e) {
            return $this->handleAIException($e, 'project_completion', [
                'project_id' => $project->id,
            ]);
        }
    }

    /**
     * Suggest next tasks for a project.
     */
    public function suggestTasks(Project $project)
    {
        $this->authorize('view', $project);

        try {
            $suggestions = $this->taskAI->suggestNextTasks($project);

            Log::info('AI task suggestions generated', [
                'user_id' => auth()->id(),
                'project_id' => $project->id,
                'suggestions_count' => count($suggestions),
            ]);

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
            ]);
        } catch (\Exception $e) {
            return $this->handleAIException($e, 'suggest_tasks', [
                'project_id' => $project->id,
            ]);
        }
    }

    /**
     * Get AI provider status.
     */
    public function status()
    {
        try {
            $available = $this->ai->getAvailableProviders();
            $current = $this->ai->getProvider()->getName();

            return response()->json([
                'success' => true,
                'is_available' => $this->ai->isAvailable(),
                'current_provider' => $current,
                'available_providers' => array_keys($available),
            ]);
        } catch (\Exception $e) {
            Log::error('AI status check failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'is_available' => false,
                'error' => 'Unable to check AI service status',
            ], 500);
        }
    }
}
