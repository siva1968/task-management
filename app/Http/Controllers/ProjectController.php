<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $query = Project::with('owner');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('client_name', 'like', '%' . $request->search . '%');
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $projects = $query->paginate(15);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('projects.create', compact('users'));
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_name' => 'nullable|string|max:255',
            'status' => 'required|in:planning,active,on_hold,completed,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $project = Project::create([
            'tenant_id' => auth()->user()->tenant_id,
            'owner_id' => $validated['owner_id'] ?? auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'status' => $validated['status'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'budget' => $validated['budget'] ?? null,
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $project->load(['owner', 'tasks.assignedUsers']);

        // Get project statistics
        $stats = [
            'total_tasks' => $project->tasks->count(),
            'completed_tasks' => $project->tasks->where('status', 'completed')->count(),
            'in_progress_tasks' => $project->tasks->where('status', 'in_progress')->count(),
            'overdue_tasks' => $project->tasks->filter->isOverdue()->count(),
            'completion_percentage' => $project->completionPercentage(),
            'total_estimated_hours' => $project->totalEstimatedHours(),
            'total_actual_hours' => $project->totalActualHours(),
        ];

        // Get task breakdown by status
        $tasksByStatus = $project->tasks->groupBy('status');

        // Get task breakdown by priority
        $tasksByPriority = $project->tasks->groupBy('priority');

        return view('projects.show', compact('project', 'stats', 'tasksByStatus', 'tasksByPriority'));
    }

    /**
     * Show the form for editing the project.
     */
    public function edit(Project $project)
    {
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return view('projects.edit', compact('project', 'users'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_name' => 'nullable|string|max:255',
            'status' => 'required|in:planning,active,on_hold,completed,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'owner_id' => 'nullable|exists:users,id',
        ]);

        $project->update([
            'owner_id' => $validated['owner_id'] ?? $project->owner_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'client_name' => $validated['client_name'] ?? null,
            'status' => $validated['status'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'budget' => $validated['budget'] ?? null,
        ]);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}
