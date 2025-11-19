<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Services\AI\AIService;
use App\Services\AI\ProjectAIService;
use App\Services\AI\TaskAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * Break down a task into subtasks.
     */
    public function breakdownTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $subtasks = $this->taskAI->breakdownTask(
                $request->title,
                $request->description
            );

            return response()->json([
                'success' => true,
                'subtasks' => $subtasks,
            ]);
        } catch (\Exception $e) {
            Log::error('AI breakdown task error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate subtasks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Estimate hours for a task.
     */
    public function estimateHours(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        try {
            $hours = $this->taskAI->estimateHours(
                $request->title,
                $request->description,
                $request->priority
            );

            return response()->json([
                'success' => true,
                'estimated_hours' => $hours,
            ]);
        } catch (\Exception $e) {
            Log::error('AI estimate hours error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to estimate hours: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Suggest priority for a task.
     */
    public function suggestPriority(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        try {
            $priority = $this->taskAI->suggestPriority(
                $request->title,
                $request->description,
                $request->due_date
            );

            return response()->json([
                'success' => true,
                'priority' => $priority,
            ]);
        } catch (\Exception $e) {
            Log::error('AI suggest priority error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to suggest priority: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enhance task description.
     */
    public function enhanceDescription(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $enhanced = $this->taskAI->enhanceDescription(
                $request->title,
                $request->description
            );

            return response()->json([
                'success' => true,
                'description' => $enhanced,
            ]);
        } catch (\Exception $e) {
            Log::error('AI enhance description error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to enhance description: ' . $e->getMessage(),
            ], 500);
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

            return response()->json([
                'success' => true,
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            Log::error('AI project summary error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate summary: ' . $e->getMessage(),
            ], 500);
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

            return response()->json([
                'success' => true,
                'risks' => $risks,
            ]);
        } catch (\Exception $e) {
            Log::error('AI project risks error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to analyze risks: ' . $e->getMessage(),
            ], 500);
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

            return response()->json([
                'success' => true,
                'prediction' => $prediction,
            ]);
        } catch (\Exception $e) {
            Log::error('AI project completion error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to predict completion: ' . $e->getMessage(),
            ], 500);
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

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions,
            ]);
        } catch (\Exception $e) {
            Log::error('AI suggest tasks error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to suggest tasks: ' . $e->getMessage(),
            ], 500);
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
            return response()->json([
                'success' => false,
                'is_available' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
