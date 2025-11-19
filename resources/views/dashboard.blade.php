@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-600">Welcome back, {{ auth()->user()->name }}</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Projects -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Projects</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $stats['total_projects'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="font-medium text-indigo-600">{{ $stats['active_projects'] }}</span>
                    <span class="text-gray-500"> active</span>
                </div>
            </div>
        </div>

        <!-- My Tasks -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">My Tasks</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $stats['my_tasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <a href="{{ route('tasks.index', ['assigned_to' => auth()->id()]) }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        View all →
                    </a>
                </div>
            </div>
        </div>

        <!-- Completed Tasks -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Completed Tasks</dt>
                            <dd class="text-2xl font-semibold text-gray-900">{{ $stats['completed_tasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    <span class="text-gray-500">of {{ $stats['total_tasks'] }} total</span>
                </div>
            </div>
        </div>

        <!-- Overdue Tasks -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Overdue Tasks</dt>
                            <dd class="text-2xl font-semibold text-red-600">{{ $stats['overdue_tasks'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-5 py-3">
                <div class="text-sm">
                    @if($stats['overdue_tasks'] > 0)
                    <a href="{{ route('tasks.index') }}" class="font-medium text-red-600 hover:text-red-500">
                        Review tasks →
                    </a>
                    @else
                    <span class="text-gray-500">Great job!</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- My Active Tasks -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-5 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">My Active Tasks</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($myTasks as $task)
                <a href="{{ route('tasks.show', $task) }}" class="block hover:bg-gray-50 px-5 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $task->title }}</p>
                            <p class="text-sm text-gray-500">
                                {{ $task->project->name }}
                                @if($task->due_date)
                                • Due: {{ $task->due_date->format('M d, Y') }}
                                @endif
                            </p>
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
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </div>
                    </div>
                </a>
                @empty
                <div class="px-5 py-4 text-center text-gray-500">
                    No active tasks assigned to you
                </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Projects -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Recent Projects</h3>
                <a href="{{ route('projects.create') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    New Project
                </a>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentProjects as $project)
                <a href="{{ route('projects.show', $project) }}" class="block hover:bg-gray-50 px-5 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $project->name }}</p>
                            <p class="text-sm text-gray-500">
                                @if($project->client_name)
                                {{ $project->client_name }} •
                                @endif
                                {{ $project->tasks->count() }} tasks
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                @if($project->status === 'active') bg-green-100 text-green-800
                                @elseif($project->status === 'on_hold') bg-yellow-100 text-yellow-800
                                @elseif($project->status === 'completed') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </div>
                    </div>
                </a>
                @empty
                <div class="px-5 py-4 text-center text-gray-500">
                    No projects yet. <a href="{{ route('projects.create') }}" class="text-indigo-600 hover:text-indigo-500">Create your first project</a>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    @if($overdueTasks->count() > 0)
    <!-- Overdue Tasks -->
    <div class="mt-6 bg-white shadow rounded-lg">
        <div class="px-5 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-red-600">Overdue Tasks</h3>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($overdueTasks as $task)
            <a href="{{ route('tasks.show', $task) }}" class="block hover:bg-gray-50 px-5 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $task->title }}</p>
                        <p class="text-sm text-red-600">
                            Overdue by {{ $task->due_date->diffForHumans() }}
                            • {{ $task->project->name }}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
