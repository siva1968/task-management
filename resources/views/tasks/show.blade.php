@extends('layouts.app')

@section('title', $task->title)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-start">
        <div>
            <div class="flex items-center space-x-3">
                <h1 class="text-2xl font-semibold text-gray-900">{{ $task->title }}</h1>
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    @if($task->status === 'completed') bg-green-100 text-green-800
                    @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                    @elseif($task->status === 'review') bg-purple-100 text-purple-800
                    @elseif($task->status === 'cancelled') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                </span>
                <span class="px-3 py-1 text-sm font-medium rounded-full
                    @if($task->priority === 'urgent') bg-red-100 text-red-800
                    @elseif($task->priority === 'high') bg-orange-100 text-orange-800
                    @elseif($task->priority === 'medium') bg-yellow-100 text-yellow-800
                    @else bg-green-100 text-green-800
                    @endif">
                    {{ ucfirst($task->priority) }} Priority
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-600">
                Created {{ $task->created_at->format('M d, Y') }}
                @if($task->creator)
                by {{ $task->creator->name }}
                @endif
            </p>
        </div>
        <a href="{{ route('tasks.edit', $task) }}"
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            Edit Task
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Description</h2>
                @if($task->description)
                <div class="prose prose-sm max-w-none text-gray-700">
                    {!! nl2br(e($task->description)) !!}
                </div>
                @else
                <p class="text-gray-500 italic">No description provided</p>
                @endif
            </div>

            <!-- Project Info -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Project</h2>
                <a href="{{ route('projects.show', $task->project) }}"
                   class="text-indigo-600 hover:text-indigo-800 font-medium">
                    {{ $task->project->name }}
                </a>
                @if($task->project->client_name)
                <p class="mt-1 text-sm text-gray-600">Client: {{ $task->project->client_name }}</p>
                @endif
            </div>

            <!-- Assigned Users -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Assigned To</h2>
                @if($task->assignedUsers->count() > 0)
                <div class="space-y-3">
                    @foreach($task->assignedUsers as $user)
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                            <span class="text-indigo-600 font-medium text-sm">
                                {{ substr($user->name, 0, 2) }}
                            </span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 italic">No users assigned</p>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Details Card -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Details</h2>
                <dl class="space-y-3">
                    <!-- Due Date -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($task->due_date)
                            <span class="@if($task->isOverdue()) text-red-600 font-medium @endif">
                                {{ $task->due_date->format('M d, Y') }}
                                @if($task->isOverdue())
                                <span class="text-xs">(Overdue)</span>
                                @endif
                            </span>
                            @else
                            <span class="text-gray-500">Not set</span>
                            @endif
                        </dd>
                    </div>

                    <!-- Estimated Hours -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Estimated Hours</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $task->estimated_hours ?? 'Not set' }}
                        </dd>
                    </div>

                    <!-- Actual Hours -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Actual Hours</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $task->actual_hours }}
                            @if($task->estimated_hours && $task->actual_hours > 0)
                            <span class="text-xs text-gray-500">
                                ({{ round(($task->actual_hours / $task->estimated_hours) * 100) }}% of estimate)
                            </span>
                            @endif
                        </dd>
                    </div>

                    <!-- Completed At -->
                    @if($task->completed_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $task->completed_at->format('M d, Y g:i A') }}
                        </dd>
                    </div>
                    @endif

                    <!-- Last Updated -->
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $task->updated_at->diffForHumans() }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
                <div class="space-y-2">
                    @if($task->status !== 'completed')
                    <form action="{{ route('tasks.update', $task) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="project_id" value="{{ $task->project_id }}">
                        <input type="hidden" name="title" value="{{ $task->title }}">
                        <input type="hidden" name="description" value="{{ $task->description }}">
                        <input type="hidden" name="status" value="completed">
                        <input type="hidden" name="priority" value="{{ $task->priority }}">
                        <input type="hidden" name="estimated_hours" value="{{ $task->estimated_hours }}">
                        <input type="hidden" name="actual_hours" value="{{ $task->actual_hours }}">
                        <input type="hidden" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}">
                        <button type="submit"
                                class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                            Mark as Completed
                        </button>
                    </form>
                    @endif

                    <a href="{{ route('tasks.edit', $task) }}"
                       class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 text-center">
                        Edit Task
                    </a>

                    <a href="{{ route('tasks.index') }}"
                       class="block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 text-center">
                        Back to Tasks
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
