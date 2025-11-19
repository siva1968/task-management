<?php

namespace App\Jobs\AI;

use App\Models\Project;
use App\Services\AI\ProjectAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AnalyzeProjectRisksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 10;

    protected Project $project;
    protected string $cacheKey;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->cacheKey = "ai_risks_job_{$project->id}";
    }

    /**
     * Execute the job.
     */
    public function handle(ProjectAIService $projectAI): void
    {
        try {
            Log::info('Starting project risk analysis job', [
                'project_id' => $this->project->id,
            ]);

            // Mark job as processing
            Cache::put("{$this->cacheKey}_status", 'processing', 300);

            $risks = $projectAI->analyzeRisks($this->project);

            // Store result in cache
            Cache::put($this->cacheKey, [
                'status' => 'completed',
                'risks' => $risks,
                'analyzed_at' => now()->toIso8601String(),
            ], 3600);

            Log::info('Project risk analysis completed', [
                'project_id' => $this->project->id,
                'risks_count' => count($risks),
            ]);
        } catch (\Exception $e) {
            Log::error('Project risk analysis failed', [
                'project_id' => $this->project->id,
                'error' => $e->getMessage(),
            ]);

            // Store error in cache
            Cache::put($this->cacheKey, [
                'status' => 'failed',
                'error' => 'Failed to analyze risks. Please try again.',
                'failed_at' => now()->toIso8601String(),
            ], 300);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Project risk analysis job permanently failed', [
            'project_id' => $this->project->id,
            'error' => $exception->getMessage(),
        ]);

        Cache::put($this->cacheKey, [
            'status' => 'failed',
            'error' => 'Unable to analyze risks after multiple attempts.',
            'failed_at' => now()->toIso8601String(),
        ], 300);
    }
}
