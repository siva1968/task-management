<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the dashboard.
     */
    public function index()
    {
        $user = auth()->user();

        // Get statistics
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'total_tasks' => Task::count(),
            'my_tasks' => $user->assignedTasks()->count(),
            'completed_tasks' => Task::where('status', 'completed')->count(),
            'overdue_tasks' => Task::overdue()->count(),
            'in_progress_tasks' => Task::where('status', 'in_progress')->count(),
        ];

        // Get recent projects
        $recentProjects = Project::with('owner')
            ->latest()
            ->limit(5)
            ->get();

        // Get my tasks
        $myTasks = $user->assignedTasks()
            ->with(['project', 'creator'])
            ->where('status', '!=', 'completed')
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        // Get overdue tasks
        $overdueTasks = Task::overdue()
            ->with(['project', 'assignedUsers'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        // Get project completion stats
        $projectStats = Project::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Get task priority distribution
        $taskPriorityStats = Task::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        return view('dashboard', compact(
            'stats',
            'recentProjects',
            'myTasks',
            'overdueTasks',
            'projectStats',
            'taskPriorityStats'
        ));
    }
}
