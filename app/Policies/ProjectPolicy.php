<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('projects.view');
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        // Must have permission and project must belong to user's tenant
        return $user->hasPermission('projects.view') && $project->tenant_id === $user->tenant_id;
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('projects.create');
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        // Must be in same tenant
        if ($project->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $user->hasPermission('projects.edit');
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        // Must be in same tenant
        if ($project->tenant_id !== $user->tenant_id) {
            return false;
        }

        return $user->hasPermission('projects.delete');
    }

    /**
     * Determine whether the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        return $user->hasPermission('projects.delete') && $project->tenant_id === $user->tenant_id;
    }

    /**
     * Determine whether the user can permanently delete the project.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->isAdmin() && $project->tenant_id === $user->tenant_id;
    }
}
