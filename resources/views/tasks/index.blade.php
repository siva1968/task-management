@extends('layouts.app')

@section('title', 'Tasks')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Tasks</h1>
            <p class="mt-1 text-sm text-gray-600">Manage and track all your tasks</p>
        </div>
        <a href="{{ route('tasks.create') }}"
           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            Create Task
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('tasks.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search tasks..."
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Project Filter -->
                <div>
                    <label for="project" class="block text-sm font-medium text-gray-700">Project</label>
                    <select name="project" id="project"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="backlog" {{ request('status') === 'backlog' ? 'selected' : '' }}>Backlog</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Review</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                    <select name="priority" id="priority"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Priorities</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <a href="{{ route('tasks.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    Clear Filters
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Tasks List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            @forelse($tasks as $task)
            <li>
                <a href="{{ route('tasks.show', $task) }}" class="block hover:bg-gray-50">
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-indigo-600 truncate">
                                    {{ $task->title }}
                                </p>
                                <div class="mt-2 flex items-center text-sm text-gray-500">
                                    <span>{{ $task->project->name }}</span>
                                    @if($task->due_date)
                                    <span class="mx-2">•</span>
                                    <span class="@if($task->isOverdue()) text-red-600 @endif">
                                        Due: {{ $task->due_date->format('M d, Y') }}
                                    </span>
                                    @endif
                                    @if($task->assigned_users_count > 0)
                                    <span class="mx-2">•</span>
                                    <span>
                                        Assigned to: {{ $task->assignedUsers->pluck('name')->join(', ') }}
                                    </span>
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
                    </div>
                </a>
            </li>
            @empty
            <li class="px-4 py-12 text-center">
                <div class="text-gray-500">
                    <p class="text-lg font-medium">No tasks found</p>
                    <p class="mt-1">Get started by creating a new task</p>
                    <a href="{{ route('tasks.create') }}"
                       class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Create Task
                    </a>
                </div>
            </li>
            @endforelse
        </ul>
    </div>

    <!-- Pagination -->
    @if($tasks->hasPages())
    <div class="mt-6">
        {{ $tasks->links() }}
    </div>
    @endif
</div>
@endsection
