<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of tasks.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $query = Task::with(['project', 'assignedUsers', 'creator']);

        // Filter by project
        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned user
        if ($request->filled('assigned_to')) {
            $query->whereHas('assignedUsers', function ($q) use ($request) {
                $q->where('users.id', $request->assigned_to);
            });
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sort - validate column to prevent SQL injection
        $allowedSortColumns = ['created_at', 'title', 'due_date', 'status', 'priority', 'updated_at'];
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);

        $tasks = $query->paginate(20);

        // Get filter options
        $projects = Project::orderBy('name')->get();
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('tasks.index', compact('tasks', 'projects', 'users'));
    }

    /**
     * Show the form for creating a new task.
     */
    public function create()
    {
        $this->authorize('create', Task::class);

        $projects = Project::orderBy('name')->get();
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('tasks.create', compact('projects', 'users'));
    }

    /**
     * Store a newly created task.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Task::class);

        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'project_id' => [
                'required',
                'exists:projects,id',
                function ($attribute, $value, $fail) use ($tenantId) {
                    $project = \App\Models\Project::withoutGlobalScopes()->find($value);
                    if (!$project || $project->tenant_id !== $tenantId) {
                        $fail('The selected project is invalid.');
                    }
                },
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:backlog,in_progress,review,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date|after_or_equal:today',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => [
                'exists:users,id',
                function ($attribute, $value, $fail) use ($tenantId) {
                    $user = \App\Models\User::find($value);
                    if (!$user || $user->tenant_id !== $tenantId) {
                        $fail('The selected user is invalid.');
                    }
                },
            ],
        ]);

        $task = Task::create([
            'tenant_id' => auth()->user()->tenant_id,
            'project_id' => $validated['project_id'],
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        // Assign users
        if (!empty($validated['assigned_users'])) {
            $assignmentData = [];
            foreach ($validated['assigned_users'] as $userId) {
                $assignmentData[$userId] = [
                    'assigned_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $task->assignedUsers()->attach($assignmentData);
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['project', 'assignedUsers', 'creator']);

        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the task.
     */
    public function edit(Task $task)
    {
        $this->authorize('update', $task);

        $projects = Project::orderBy('name')->get();
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        $task->load('assignedUsers');

        return view('tasks.edit', compact('task', 'projects', 'users'));
    }

    /**
     * Update the specified task.
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'project_id' => [
                'required',
                'exists:projects,id',
                function ($attribute, $value, $fail) use ($tenantId) {
                    $project = \App\Models\Project::withoutGlobalScopes()->find($value);
                    if (!$project || $project->tenant_id !== $tenantId) {
                        $fail('The selected project is invalid.');
                    }
                },
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:backlog,in_progress,review,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'assigned_users' => 'nullable|array',
            'assigned_users.*' => [
                'exists:users,id',
                function ($attribute, $value, $fail) use ($tenantId) {
                    $user = \App\Models\User::find($value);
                    if (!$user || $user->tenant_id !== $tenantId) {
                        $fail('The selected user is invalid.');
                    }
                },
            ],
        ]);

        $task->update([
            'project_id' => $validated['project_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'priority' => $validated['priority'],
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'actual_hours' => $validated['actual_hours'] ?? 0,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        // Sync assigned users with assigned_by tracking
        if (isset($validated['assigned_users'])) {
            $syncData = [];
            foreach ($validated['assigned_users'] as $userId) {
                // Keep existing assigned_by if user was already assigned, otherwise set current user
                $existing = $task->assignedUsers()->where('user_id', $userId)->first();
                $syncData[$userId] = [
                    'assigned_by' => $existing ? $existing->pivot->assigned_by : auth()->id(),
                    'updated_at' => now(),
                ];
            }
            $task->assignedUsers()->sync($syncData);
        } else {
            // If no users selected, remove all assignments
            $task->assignedUsers()->sync([]);
        }

        return redirect()
            ->route('tasks.show', $task)
            ->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified task.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }

    /**
     * Mark task as complete (quick action).
     */
    public function markAsComplete(Task $task)
    {
        $this->authorize('update', $task);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Task marked as completed.');
    }

    /**
     * Update task status only (quick action).
     */
    public function updateStatus(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $request->validate([
            'status' => 'required|in:backlog,in_progress,review,completed,cancelled',
        ]);

        $task->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Task status updated successfully.');
    }
}
