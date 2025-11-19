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
                <div class="flex items-center justify-between mb-1">
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Description
                    </label>
                    <button type="button" onclick="enhanceDescription()" id="ai-enhance-btn"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        AI Enhance
                    </button>
                </div>
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
                    <div class="flex items-center justify-between mb-1">
                        <label for="priority" class="block text-sm font-medium text-gray-700">
                            Priority <span class="text-red-500">*</span>
                        </label>
                        <button type="button" onclick="suggestPriority()" id="ai-priority-btn"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            AI Suggest
                        </button>
                    </div>
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
                    <div class="flex items-center justify-between mb-1">
                        <label for="estimated_hours" class="block text-sm font-medium text-gray-700">
                            Estimated Hours
                        </label>
                        <button type="button" onclick="estimateHours()" id="ai-estimate-btn"
                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 rounded">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            AI Estimate
                        </button>
                    </div>
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

            <!-- AI Task Breakdown -->
            <div class="border border-indigo-200 rounded-lg p-4 bg-indigo-50">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-900">AI Task Breakdown</h3>
                    <button type="button" onclick="breakdownTask()" id="ai-breakdown-btn"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Generate Subtasks
                    </button>
                </div>
                <p class="text-xs text-gray-600 mb-3">AI can break down this task into smaller, actionable subtasks.</p>
                <div id="subtasks-container" class="hidden">
                    <div class="space-y-2 max-h-64 overflow-y-auto" id="subtasks-list"></div>
                    <p class="text-xs text-gray-500 mt-2 italic">Note: These are AI suggestions. You can create them manually as separate tasks.</p>
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

<script>
// Get CSRF token
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// AI Enhance Description
async function enhanceDescription() {
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    const btn = document.getElementById('ai-enhance-btn');

    if (!title) {
        alert('Please enter a task title first');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Enhancing...';

    try {
        const response = await fetch('/ai/task/enhance-description', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ title, description })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('description').value = data.description;
        } else {
            alert('AI Error: ' + (data.error || 'Failed to enhance description'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>AI Enhance';
    }
}

// AI Suggest Priority
async function suggestPriority() {
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    const dueDate = document.getElementById('due_date').value;
    const btn = document.getElementById('ai-priority-btn');

    if (!title) {
        alert('Please enter a task title first');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Suggesting...';

    try {
        const response = await fetch('/ai/task/suggest-priority', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ title, description, due_date: dueDate })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('priority').value = data.priority;
        } else {
            alert('AI Error: ' + (data.error || 'Failed to suggest priority'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>AI Suggest';
    }
}

// AI Estimate Hours
async function estimateHours() {
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    const priority = document.getElementById('priority').value;
    const btn = document.getElementById('ai-estimate-btn');

    if (!title) {
        alert('Please enter a task title first');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Estimating...';

    try {
        const response = await fetch('/ai/task/estimate-hours', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ title, description, priority })
        });

        const data = await response.json();

        if (data.success) {
            document.getElementById('estimated_hours').value = data.estimated_hours;
        } else {
            alert('AI Error: ' + (data.error || 'Failed to estimate hours'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>AI Estimate';
    }
}

// AI Task Breakdown
async function breakdownTask() {
    const title = document.getElementById('title').value;
    const description = document.getElementById('description').value;
    const btn = document.getElementById('ai-breakdown-btn');
    const container = document.getElementById('subtasks-container');
    const list = document.getElementById('subtasks-list');

    if (!title) {
        alert('Please enter a task title first');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generating...';

    try {
        const response = await fetch('/ai/task/breakdown', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ title, description })
        });

        const data = await response.json();

        if (data.success && data.subtasks && data.subtasks.length > 0) {
            list.innerHTML = data.subtasks.map((subtask, index) => `
                <div class="bg-white p-3 rounded border border-gray-200">
                    <div class="flex items-start">
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-medium mr-2 flex-shrink-0">
                            ${index + 1}
                        </span>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">${escapeHtml(subtask.title)}</h4>
                            ${subtask.description ? `<p class="text-xs text-gray-600 mt-1">${escapeHtml(subtask.description)}</p>` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
            container.classList.remove('hidden');
        } else {
            alert('AI Error: ' + (data.error || 'Failed to generate subtasks'));
        }
    } catch (error) {
        alert('Error: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Generate Subtasks';
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection
