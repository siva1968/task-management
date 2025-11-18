@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Projects</h1>
            <p class="mt-1 text-sm text-gray-600">Manage all your projects</p>
        </div>
        <a href="{{ route('projects.create') }}"
           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            Create Project
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('projects.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search projects..."
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Statuses</option>
                        <option value="planning" {{ request('status') === 'planning' ? 'selected' : '' }}>Planning</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <a href="{{ route('projects.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    Clear Filters
                </a>
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Projects Grid -->
    @if($projects->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($projects as $project)
        <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-medium text-gray-900 truncate">
                        <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">
                            {{ $project->name }}
                        </a>
                    </h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        @if($project->status === 'active') bg-green-100 text-green-800
                        @elseif($project->status === 'on_hold') bg-yellow-100 text-yellow-800
                        @elseif($project->status === 'completed') bg-blue-100 text-blue-800
                        @elseif($project->status === 'archived') bg-gray-100 text-gray-800
                        @else bg-purple-100 text-purple-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </div>

                @if($project->description)
                <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                    {{ $project->description }}
                </p>
                @endif

                <div class="space-y-2 text-sm text-gray-500">
                    @if($project->client_name)
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        {{ $project->client_name }}
                    </div>
                    @endif

                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ $project->tasks->count() }} tasks
                    </div>

                    @if($project->owner)
                    <div class="flex items-center">
                        <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ $project->owner->name }}
                    </div>
                    @endif
                </div>

                @if($project->start_date || $project->end_date)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="text-xs text-gray-500">
                        @if($project->start_date)
                        Start: {{ $project->start_date->format('M d, Y') }}
                        @endif
                        @if($project->start_date && $project->end_date)
                        -
                        @endif
                        @if($project->end_date)
                        End: {{ $project->end_date->format('M d, Y') }}
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <div class="bg-gray-50 px-6 py-3">
                <a href="{{ route('projects.show', $project) }}"
                   class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    View Details →
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($projects->hasPages())
    <div class="mt-6">
        {{ $projects->links() }}
    </div>
    @endif

    @else
    <!-- Empty State -->
    <div class="bg-white shadow rounded-lg p-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No projects</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new project.</p>
        <div class="mt-6">
            <a href="{{ route('projects.create') }}"
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                Create Project
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
