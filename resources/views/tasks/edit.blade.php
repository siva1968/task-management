@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Task</h1>
        <p class="mt-1 text-sm text-gray-600">Update task details</p>
    </div>

    <div class="bg-white shadow rounded-lg">
        <form action="{{ route('tasks.update', $task) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Project -->
            <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700">
                    Project <span class="text-red-500">*</span>
                </label>
                <select name="project_id" id="project_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('project_id') border-red-500 @enderror">
                    <option value="">Select a project</option>
                    @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                    @endforeach
                </select>
                @error('project_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">
                    Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" required value="{{ old('title', $task->title) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('title') border-red-500 @enderror">
                @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    Description
                </label>
                <textarea name="description" id="description" rows="4"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('description') border-red-500 @enderror">{{ old('description', $task->description) }}</textarea>
                @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" id="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="backlog" {{ old('status', $task->status) === 'backlog' ? 'selected' : '' }}>Backlog</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="review" {{ old('status', $task->status) === 'review' ? 'selected' : '' }}>Review</option>
                        <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status', $task->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">
                        Priority <span class="text-red-500">*</span>
                    </label>
                    <select name="priority" id="priority" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority', $task->priority) === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <!-- Estimated Hours -->
                <div>
                    <label for="estimated_hours" class="block text-sm font-medium text-gray-700">
                        Estimated Hours
                    </label>
                    <input type="number" name="estimated_hours" id="estimated_hours" step="0.5" min="0"
                           value="{{ old('estimated_hours', $task->estimated_hours) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Actual Hours -->
                <div>
                    <label for="actual_hours" class="block text-sm font-medium text-gray-700">
                        Actual Hours
                    </label>
                    <input type="number" name="actual_hours" id="actual_hours" step="0.5" min="0"
                           value="{{ old('actual_hours', $task->actual_hours) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>

                <!-- Due Date -->
                <div class="sm:col-span-2">
                    <label for="due_date" class="block text-sm font-medium text-gray-700">
                        Due Date
                    </label>
                    <input type="date" name="due_date" id="due_date"
                           value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <!-- Assigned Users -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Assign To
                </label>
                <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-md p-3">
                    @foreach($users as $user)
                    <div class="flex items-center">
                        <input type="checkbox" name="assigned_users[]" value="{{ $user->id }}"
                               id="user_{{ $user->id }}"
                               {{ (is_array(old('assigned_users')) && in_array($user->id, old('assigned_users'))) || $task->assignedUsers->contains($user->id) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="user_{{ $user->id }}" class="ml-2 text-sm text-gray-700">
                            {{ $user->name }} ({{ $user->email }})
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center pt-4 border-t">
                <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this task?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                        Delete Task
                    </button>
                </form>

                <div class="flex space-x-3">
                    <a href="{{ route('tasks.show', $task) }}"
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Update Task
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
