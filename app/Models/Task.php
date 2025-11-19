<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'project_id',
        'created_by',
        'title',
        'description',
        'status',
        'priority',
        'estimated_hours',
        'actual_hours',
        'due_date',
        'completed_at',
        'position',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'position' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function booted()
    {
        static::addGlobalScope('tenant', function ($query) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
        });

        static::updating(function ($task) {
            if ($task->isDirty('status') && $task->status === 'completed' && !$task->completed_at) {
                $task->completed_at = now();
            }

            if ($task->isDirty('status') && $task->status !== 'completed' && $task->completed_at) {
                $task->completed_at = null;
            }
        });
    }

    /**
     * Get the tenant that owns the task.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the project that owns the task.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user who created the task.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the users assigned to the task.
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'task_assignments')
            ->withPivot('assigned_by')
            ->withTimestamps();
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date &&
               $this->due_date->isPast() &&
               $this->status !== 'completed';
    }

    /**
     * Check if task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Assign user to task.
     */
    public function assignUser(User $user, ?User $assignedBy = null)
    {
        return $this->assignedUsers()->syncWithoutDetaching([
            $user->id => ['assigned_by' => $assignedBy?->id ?? auth()->id()]
        ]);
    }

    /**
     * Unassign user from task.
     */
    public function unassignUser(User $user)
    {
        return $this->assignedUsers()->detach($user);
    }

    /**
     * Scope for filtering by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', 'completed');
    }

    /**
     * Scope for completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for tasks assigned to a specific user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->whereHas('assignedUsers', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    /**
     * Scope for tasks due soon (within X days).
     */
    public function scopeDueSoon($query, int $days = 7)
    {
        return $query->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays($days))
            ->where('status', '!=', 'completed');
    }

    /**
     * Check if task is assigned to a specific user.
     */
    public function isAssignedTo(User $user): bool
    {
        return $this->assignedUsers->contains($user->id);
    }

    /**
     * Get remaining hours (estimated minus actual).
     */
    public function getRemainingHours(): float
    {
        if (!$this->estimated_hours) {
            return 0;
        }

        return max(0, $this->estimated_hours - $this->actual_hours);
    }

    /**
     * Get status badge color for UI.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'backlog' => 'gray',
            'in_progress' => 'blue',
            'review' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get priority badge color for UI.
     */
    public function getPriorityBadgeColorAttribute(): string
    {
        return match($this->priority) {
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray',
        };
    }
}
