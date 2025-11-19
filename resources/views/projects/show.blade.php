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

            <!-- AI Project Summary -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">AI Project Summary</h2>
                    <div class="flex items-center space-x-2">
                        <select id="summary-period" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="weekly">Weekly</option>
                            <option value="daily">Daily</option>
                            <option value="monthly">Monthly</option>
                        </select>
                        <button onclick="generateSummary()" id="generate-summary-btn"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Generate
                        </button>
                    </div>
                </div>
                <div id="ai-summary-content" class="prose prose-sm max-w-none text-gray-700">
                    <p class="text-gray-500 italic">Click "Generate" to create an AI-powered project summary</p>
                </div>
            </div>

            <!-- AI Risk Analysis -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">AI Risk Analysis</h2>
                    <button onclick="analyzeRisks()" id="analyze-risks-btn"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Analyze
                    </button>
                </div>
                <div id="ai-risks-content" class="space-y-3">
                    <p class="text-gray-500 italic text-sm">Click "Analyze" to identify potential project risks</p>
                </div>
            </div>

            <!-- AI Completion Prediction -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Completion Prediction</h2>
                    <button onclick="predictCompletion()" id="predict-completion-btn"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Predict
                    </button>
                </div>
                <div id="ai-completion-content">
                    <p class="text-gray-500 italic text-sm">Click "Predict" to estimate project completion date</p>
                </div>
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

<script>
// Markdown converter (simple version)
function convertMarkdownToHTML(markdown) {
    return markdown
        // Headers
        .replace(/^### (.*$)/gim, '<h3 class="text-lg font-semibold mt-4 mb-2">$1</h3>')
        .replace(/^## (.*$)/gim, '<h2 class="text-xl font-semibold mt-6 mb-3">$1</h2>')
        .replace(/^# (.*$)/gim, '<h1 class="text-2xl font-semibold mt-8 mb-4">$1</h1>')
        // Bold
        .replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>')
        // Italic
        .replace(/\*(.*?)\*/gim, '<em>$1</em>')
        // Bullet lists
        .replace(/^\- (.*$)/gim, '<li class="ml-4">$1</li>')
        .replace(/(<li.*<\/li>)/gim, '<ul class="list-disc list-inside mb-2">$1</ul>')
        // Line breaks
        .replace(/\n/gim, '<br>');
}

// Generate AI Project Summary
async function generateSummary() {
    const projectId = {{ $project->id }};
    const period = document.getElementById('summary-period').value;
    const btn = document.getElementById('generate-summary-btn');
    const content = document.getElementById('ai-summary-content');

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Generating...';
    content.innerHTML = '<p class="text-gray-500 italic">AI is generating your project summary...</p>';

    try {
        const response = await fetch(`/ai/project/${projectId}/summary?period=${period}`);
        const data = await response.json();

        if (data.success) {
            content.innerHTML = convertMarkdownToHTML(data.summary);
        } else {
            content.innerHTML = '<p class="text-red-600">Error: ' + (data.error || 'Failed to generate summary') + '</p>';
        }
    } catch (error) {
        content.innerHTML = '<p class="text-red-600">Error: ' + error.message + '</p>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>Generate';
    }
}

// Analyze Project Risks
async function analyzeRisks() {
    const projectId = {{ $project->id }};
    const btn = document.getElementById('analyze-risks-btn');
    const content = document.getElementById('ai-risks-content');

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Analyzing...';
    content.innerHTML = '<p class="text-gray-500 italic text-sm">AI is analyzing project risks...</p>';

    try {
        const response = await fetch(`/ai/project/${projectId}/risks`);
        const data = await response.json();

        if (data.success && data.risks && data.risks.length > 0) {
            content.innerHTML = data.risks.map(risk => `
                <div class="border-l-4 ${getSeverityColor(risk.severity)} bg-gray-50 p-3 rounded">
                    <div class="flex items-start">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <h4 class="text-sm font-semibold text-gray-900">${escapeHtml(risk.title)}</h4>
                                <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded ${getSeverityBadge(risk.severity)}">
                                    ${escapeHtml(risk.severity)}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">${escapeHtml(risk.description)}</p>
                            <div class="mt-2 pt-2 border-t border-gray-200">
                                <p class="text-xs text-gray-700"><strong>Mitigation:</strong> ${escapeHtml(risk.mitigation)}</p>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        } else if (data.success && (!data.risks || data.risks.length === 0)) {
            content.innerHTML = '<p class="text-green-600 text-sm">No significant risks identified. Project appears to be on track!</p>';
        } else {
            content.innerHTML = '<p class="text-red-600 text-sm">Error: ' + (data.error || 'Failed to analyze risks') + '</p>';
        }
    } catch (error) {
        content.innerHTML = '<p class="text-red-600 text-sm">Error: ' + error.message + '</p>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>Analyze';
    }
}

// Predict Project Completion
async function predictCompletion() {
    const projectId = {{ $project->id }};
    const btn = document.getElementById('predict-completion-btn');
    const content = document.getElementById('ai-completion-content');

    btn.disabled = true;
    btn.innerHTML = '<svg class="animate-spin h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Predicting...';
    content.innerHTML = '<p class="text-gray-500 italic text-sm">AI is predicting completion date...</p>';

    try {
        const response = await fetch(`/ai/project/${projectId}/completion`);
        const data = await response.json();

        if (data.success && data.prediction) {
            const pred = data.prediction;
            content.innerHTML = `
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Predicted Completion:</span>
                        <span class="text-sm font-semibold text-gray-900">${pred.predicted_date || 'N/A'}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Confidence:</span>
                        <span class="px-2 py-1 text-xs font-medium rounded ${getConfidenceColor(pred.confidence)}">
                            ${escapeHtml(pred.confidence)}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">On Track:</span>
                        <span class="text-sm font-medium ${pred.on_track ? 'text-green-600' : 'text-red-600'}">
                            ${pred.on_track ? '✓ Yes' : '✗ No'}
                        </span>
                    </div>
                    ${pred.analysis ? `
                        <div class="pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-700">${escapeHtml(pred.analysis)}</p>
                        </div>
                    ` : ''}
                </div>
            `;
        } else {
            content.innerHTML = '<p class="text-red-600 text-sm">Error: ' + (data.error || 'Failed to predict completion') + '</p>';
        }
    } catch (error) {
        content.innerHTML = '<p class="text-red-600 text-sm">Error: ' + error.message + '</p>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>Predict';
    }
}

// Helper functions
function getSeverityColor(severity) {
    const colors = {
        'low': 'border-yellow-400',
        'medium': 'border-orange-400',
        'high': 'border-red-400',
        'critical': 'border-red-600'
    };
    return colors[severity] || 'border-gray-400';
}

function getSeverityBadge(severity) {
    const badges = {
        'low': 'bg-yellow-100 text-yellow-800',
        'medium': 'bg-orange-100 text-orange-800',
        'high': 'bg-red-100 text-red-800',
        'critical': 'bg-red-200 text-red-900'
    };
    return badges[severity] || 'bg-gray-100 text-gray-800';
}

function getConfidenceColor(confidence) {
    const colors = {
        'low': 'bg-red-100 text-red-800',
        'medium': 'bg-yellow-100 text-yellow-800',
        'high': 'bg-green-100 text-green-800'
    };
    return colors[confidence] || 'bg-gray-100 text-gray-800';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endsection
