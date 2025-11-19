@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-start">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $project->name }}</h1>
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    @if($project->status === 'active') bg-green-100 text-green-800
                    @elseif($project->status === 'on_hold') bg-yellow-100 text-yellow-800
                    @elseif($project->status === 'completed') bg-blue-100 text-blue-800
                    @elseif($project->status === 'archived') bg-gray-100 text-gray-800
                    @else bg-purple-100 text-purple-800
                    @endif">
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-600">
                Created {{ $project->created_at->format('M d, Y') }}
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('tasks.create', ['project' => $project->id]) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                Add Task
            </a>
            <a href="{{ route('projects.edit', $project) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Edit Project
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Total Tasks</div>
            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $stats['total_tasks'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Completed</div>
            <div class="mt-1 text-2xl font-semibold text-green-600">{{ $stats['completed_tasks'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">In Progress</div>
            <div class="mt-1 text-2xl font-semibold text-blue-600">{{ $stats['in_progress_tasks'] }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <div class="text-sm text-gray-500">Completion</div>
            <div class="mt-1 text-2xl font-semibold text-indigo-600">{{ number_format($stats['completion_percentage'], 1) }}%</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Description</h2>
                @if($project->description)
                <div class="prose prose-sm max-w-none text-gray-700">
                    {!! nl2br(e($project->description)) !!}
                </div>
                @else
                <p class="text-gray-500 italic">No description provided</p>
                @endif
            </div>

            <!-- Tasks List -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-900">Tasks ({{ $project->tasks->count() }})</h2>
                    <a href="{{ route('tasks.create', ['project' => $project->id]) }}"
                       class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        Add Task
                    </a>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse($project->tasks->sortBy('position') as $task)
                    <a href="{{ route('tasks.show', $task) }}" class="block hover:bg-gray-50 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $task->title }}</p>
                                <div class="mt-1 flex items-center text-sm text-gray-500">
                                    @if($task->due_date)
                                    <span class="@if($task->isOverdue()) text-red-600 @endif">
                                        Due: {{ $task->due_date->format('M d, Y') }}
                                    </span>
                                    @endif
                                    @if($task->assigned_users_count > 0)
                                    <span class="mx-2">•</span>
                                    <span>{{ $task->assignedUsers->pluck('name')->join(', ') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($task->priority === 'urgent') bg-red-100 text-red-800
                                    @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                                    @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ ucfirst($task->priority) }}
                                </span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($task->status === 'completed') bg-green-100 text-green-800
                                    @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                    @elseif($task->status === 'review') bg-purple-100 text-purple-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                        </div>
                    </a>
                    @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-gray-500">No tasks yet.</p>
                        <a href="{{ route('tasks.create', ['project' => $project->id]) }}"
                           class="mt-2 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500">
                            Create your first task
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Project Details -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Project Details</h2>
                <dl class="space-y-3">
                    @if($project->client_name)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Client</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->client_name }}</dd>
                    </div>
                    @endif

                    @if($project->owner)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Project Owner</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->owner->name }}</dd>
                    </div>
                    @endif

                    @if($project->start_date)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->start_date->format('M d, Y') }}</dd>
                    </div>
                    @endif

                    @if($project->end_date)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Target End Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->end_date->format('M d, Y') }}</dd>
                    </div>
                    @endif

                    @if($project->budget)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Budget</dt>
                        <dd class="mt-1 text-sm text-gray-900">${{ number_format($project->budget, 2) }}</dd>
                    </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estimated Hours</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($stats['total_estimated_hours'], 1) }} hrs</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Actual Hours</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ number_format($stats['total_actual_hours'], 1) }} hrs</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $project->updated_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Task Breakdown -->
            @if($stats['total_tasks'] > 0)
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Task Breakdown</h2>

                <!-- By Status -->
                <div class="mb-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">By Status</h3>
                    <div class="space-y-2">
                        @foreach($tasksByStatus as $status => $tasks)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                            <span class="font-medium text-gray-900">{{ $tasks->count() }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- By Priority -->
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-2">By Priority</h3>
                    <div class="space-y-2">
                        @foreach($tasksByPriority as $priority => $tasks)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ ucfirst($priority) }}</span>
                            <span class="font-medium text-gray-900">{{ $tasks->count() }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Actions</h2>
                <div class="space-y-2">
                    <a href="{{ route('tasks.create', ['project' => $project->id]) }}"
                       class="block w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 text-center">
                        Add Task
                    </a>
                    <a href="{{ route('projects.edit', $project) }}"
                       class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 text-center">
                        Edit Project
                    </a>
                    <a href="{{ route('projects.index') }}"
                       class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 text-center">
                        Back to Projects
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
