<?php

namespace Tests\Feature\AI;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\AI\ProjectAIService;
use App\Services\AI\TaskAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class AIControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::factory()->create(['created_by' => $this->user->id]);
    }

    /** @test */
    public function it_requires_authentication_for_all_endpoints()
    {
        $endpoints = [
            ['POST', '/ai/task/breakdown'],
            ['POST', '/ai/task/estimate-hours'],
            ['POST', '/ai/task/suggest-priority'],
            ['POST', '/ai/task/enhance-description'],
            ['GET', "/ai/project/{$this->project->id}/summary"],
            ['GET', "/ai/project/{$this->project->id}/risks"],
            ['GET', "/ai/project/{$this->project->id}/completion"],
            ['GET', "/ai/project/{$this->project->id}/suggest-tasks"],
        ];

        foreach ($endpoints as [$method, $url]) {
            $response = $this->json($method, $url);
            $response->assertStatus(401);
        }
    }

    /** @test */
    public function it_validates_breakdown_task_input()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/ai/task/breakdown', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    /** @test */
    public function it_validates_max_length_for_breakdown_task()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/ai/task/breakdown', [
                'title' => str_repeat('a', 300),
                'description' => str_repeat('b', 6000),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description']);
    }

    /** @test */
    public function it_breaks_down_task_successfully()
    {
        $mock = Mockery::mock(TaskAIService::class);
        $mock->shouldReceive('breakdownTask')
            ->once()
            ->with('Test Task', 'Description')
            ->andReturn([
                ['title' => 'Subtask 1', 'description' => 'Details 1'],
                ['title' => 'Subtask 2', 'description' => 'Details 2'],
            ]);

        $this->app->instance(TaskAIService::class, $mock);

        $response = $this->actingAs($this->user)
            ->postJson('/ai/task/breakdown', [
                'title' => 'Test Task',
                'description' => 'Description',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'subtasks' => [
                    ['title' => 'Subtask 1'],
                    ['title' => 'Subtask 2'],
                ],
            ]);
    }

    /** @test */
    public function it_logs_successful_operations()
    {
        Log::shouldReceive('info')
            ->once()
            ->with('AI task breakdown successful', Mockery::type('array'));

        $mock = Mockery::mock(TaskAIService::class);
        $mock->shouldReceive('breakdownTask')->andReturn([]);

        $this->app->instance(TaskAIService::class, $mock);

        $this->actingAs($this->user)
            ->postJson('/ai/task/breakdown', [
                'title' => 'Test',
            ]);
    }

    /** @test */
    public function it_handles_ai_exceptions_gracefully()
    {
        $mock = Mockery::mock(TaskAIService::class);
        $mock->shouldReceive('breakdownTask')
            ->andThrow(new \Exception('AI service unavailable'));

        $this->app->instance(TaskAIService::class, $mock);

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::type('string'), Mockery::type('array'));

        $response = $this->actingAs($this->user)
            ->postJson('/ai/task/breakdown', [
                'title' => 'Test',
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'error' => 'An unexpected error occurred. Please try again later.',
            ]);
    }

    /** @test */
    public function it_estimates_hours_successfully()
    {
        $mock = Mockery::mock(TaskAIService::class);
        $mock->shouldReceive('estimateHours')
            ->once()
            ->with('Test Task', 'Description', 'high')
            ->andReturn(8.5);

        $this->app->instance(TaskAIService::class, $mock);

        $response = $this->actingAs($this->user)
            ->postJson('/ai/task/estimate-hours', [
                'title' => 'Test Task',
                'description' => 'Description',
                'priority' => 'high',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'estimated_hours' => 8.5,
            ]);
    }

    /** @test */
    public function it_suggests_priority_successfully()
    {
        $mock = Mockery::mock(TaskAIService::class);
        $mock->shouldReceive('suggestPriority')
            ->once()
            ->andReturn('high');

        $this->app->instance(TaskAIService::class, $mock);

        $response = $this->actingAs($this->user)
            ->postJson('/ai/task/suggest-priority', [
                'title' => 'Test Task',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'priority' => 'high',
            ]);
    }

    /** @test */
    public function it_enhances_description_successfully()
    {
        $mock = Mockery::mock(TaskAIService::class);
        $mock->shouldReceive('enhanceDescription')
            ->once()
            ->andReturn('Enhanced description with more details');

        $this->app->instance(TaskAIService::class, $mock);

        $response = $this->actingAs($this->user)
            ->postJson('/ai/task/enhance-description', [
                'title' => 'Test Task',
                'description' => 'Short description',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'description' => 'Enhanced description with more details',
            ]);
    }

    /** @test */
    public function it_requires_project_authorization_for_project_endpoints()
    {
        $otherUser = User::factory()->create();
        $otherProject = Project::factory()->create(['created_by' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/ai/project/{$otherProject->id}/summary");

        $response->assertStatus(403);
    }

    /** @test */
    public function it_generates_project_summary_successfully()
    {
        $mock = Mockery::mock(ProjectAIService::class);
        $mock->shouldReceive('generateSummary')
            ->once()
            ->with(Mockery::type(Project::class), 'weekly')
            ->andReturn('## Summary\n\nProject is on track.');

        $this->app->instance(ProjectAIService::class, $mock);

        $response = $this->actingAs($this->user)
            ->getJson("/ai/project/{$this->project->id}/summary");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'summary' => '## Summary\n\nProject is on track.',
            ]);
    }

    /** @test */
    public function it_analyzes_project_risks_successfully()
    {
        $mock = Mockery::mock(ProjectAIService::class);
        $mock->shouldReceive('analyzeRisks')
            ->once()
            ->andReturn([
                [
                    'title' => 'Resource Shortage',
                    'severity' => 'high',
                    'description' => 'Not enough developers',
                    'mitigation' => 'Hire more developers',
                ],
            ]);

        $this->app->instance(ProjectAIService::class, $mock);

        $response = $this->actingAs($this->user)
            ->getJson("/ai/project/{$this->project->id}/risks");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'risks' => [
                    ['title' => 'Resource Shortage'],
                ],
            ]);
    }

    /** @test */
    public function it_predicts_project_completion_successfully()
    {
        $mock = Mockery::mock(ProjectAIService::class);
        $mock->shouldReceive('predictCompletion')
            ->once()
            ->andReturn([
                'predicted_date' => '2025-12-31',
                'confidence' => 'medium',
                'on_track' => true,
                'analysis' => 'Project velocity is good',
            ]);

        $this->app->instance(ProjectAIService::class, $mock);

        $response = $this->actingAs($this->user)
            ->getJson("/ai/project/{$this->project->id}/completion");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'prediction' => [
                    'predicted_date' => '2025-12-31',
                    'on_track' => true,
                ],
            ]);
    }

    /** @test */
    public function it_returns_ai_provider_status()
    {
        $mock = Mockery::mock(AIService::class);
        $mock->shouldReceive('isAvailable')->andReturn(true);
        $mock->shouldReceive('getAvailableProviders')->andReturn([
            'openai' => Mockery::mock(),
            'anthropic' => Mockery::mock(),
        ]);
        $mock->shouldReceive('getProvider->getName')->andReturn('openai');

        $this->app->instance(AIService::class, $mock);

        $response = $this->actingAs($this->user)
            ->getJson('/ai/status');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_available' => true,
                'current_provider' => 'openai',
                'available_providers' => ['openai', 'anthropic'],
            ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
