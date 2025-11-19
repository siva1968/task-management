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

class GenerateProjectSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 10;

    protected Project $project;
    protected string $period;
    protected string $cacheKey;

    /**
     * Create a new job instance.
     */
    public function __construct(Project $project, string $period = 'weekly')
    {
        $this->project = $project;
        $this->period = $period;
        $this->cacheKey = "ai_summary_job_{$project->id}_{$period}";
    }

    /**
     * Execute the job.
     */
    public function handle(ProjectAIService $projectAI): void
    {
        try {
            Log::info('Starting project summary generation job', [
                'project_id' => $this->project->id,
                'period' => $this->period,
            ]);

            // Mark job as processing
            Cache::put("{$this->cacheKey}_status", 'processing', 300);

            $summary = $projectAI->generateSummary($this->project, $this->period);

            // Store result in cache
            Cache::put($this->cacheKey, [
                'status' => 'completed',
                'summary' => $summary,
                'generated_at' => now()->toIso8601String(),
            ], 3600);

            Log::info('Project summary generation completed', [
                'project_id' => $this->project->id,
                'period' => $this->period,
            ]);
        } catch (\Exception $e) {
            Log::error('Project summary generation failed', [
                'project_id' => $this->project->id,
                'period' => $this->period,
                'error' => $e->getMessage(),
            ]);

            // Store error in cache
            Cache::put($this->cacheKey, [
                'status' => 'failed',
                'error' => 'Failed to generate summary. Please try again.',
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
        Log::error('Project summary job permanently failed', [
            'project_id' => $this->project->id,
            'period' => $this->period,
            'error' => $exception->getMessage(),
        ]);

        Cache::put($this->cacheKey, [
            'status' => 'failed',
            'error' => 'Unable to generate summary after multiple attempts.',
            'failed_at' => now()->toIso8601String(),
        ], 300);
    }
}
