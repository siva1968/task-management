<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tasks.view');
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        // Must have permission and task must belong to user's tenant
        return $user->hasPermission('tasks.view') && $task->tenant_id === $user->tenant_id;
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('tasks.create');
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        // Task must belong to user's tenant
        if ($task->tenant_id !== $user->tenant_id) {
            return false;
        }

        // Admin/Manager can edit any task
        if ($user->hasPermission('tasks.edit')) {
            return true;
        }

        // Team members can only edit tasks assigned to them
        return $task->assignedUsers->contains($user);
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        // Must be in same tenant
        if ($task->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $user->hasPermission('tasks.delete');
    }

    /**
     * Determine whether the user can assign users to the task.
     */
    public function assign(User $user, Task $task): bool
    {
        // Must be in same tenant
        if ($task->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $user->hasPermission('tasks.assign');
    }

    /**
     * Determine whether the user can restore the task.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->hasPermission('tasks.delete') && $task->tenant_id === $user->tenant_id;
    }

    /**
     * Determine whether the user can permanently delete the task.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->isAdmin() && $task->tenant_id === $user->tenant_id;
    }
}
