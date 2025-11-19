<?php

namespace App\Jobs\AI;

use App\Services\AI\TaskAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BreakdownTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 10;

    protected string $title;
    protected ?string $description;
    protected string $cacheKey;

    /**
     * Create a new job instance.
     */
    public function __construct(string $title, ?string $description = null, string $cacheKey = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->cacheKey = $cacheKey ?? 'ai_breakdown_' . md5($title . $description);
    }

    /**
     * Execute the job.
     */
    public function handle(TaskAIService $taskAI): void
    {
        try {
            Log::info('Starting task breakdown job', [
                'title' => $this->title,
                'cache_key' => $this->cacheKey,
            ]);

            // Mark job as processing
            Cache::put("{$this->cacheKey}_status", 'processing', 300);

            $subtasks = $taskAI->breakdownTask($this->title, $this->description);

            // Store result in cache
            Cache::put($this->cacheKey, [
                'status' => 'completed',
                'subtasks' => $subtasks,
                'generated_at' => now()->toIso8601String(),
            ], 3600);

            Log::info('Task breakdown completed', [
                'title' => $this->title,
                'subtasks_count' => count($subtasks),
            ]);
        } catch (\Exception $e) {
            Log::error('Task breakdown failed', [
                'title' => $this->title,
                'error' => $e->getMessage(),
            ]);

            // Store error in cache
            Cache::put($this->cacheKey, [
                'status' => 'failed',
                'error' => 'Failed to generate subtasks. Please try again.',
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
        Log::error('Task breakdown job permanently failed', [
            'title' => $this->title,
            'error' => $exception->getMessage(),
        ]);

        Cache::put($this->cacheKey, [
            'status' => 'failed',
            'error' => 'Unable to generate subtasks after multiple attempts.',
            'failed_at' => now()->toIso8601String(),
        ], 300);
    }
}
