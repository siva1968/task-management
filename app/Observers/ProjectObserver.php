<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Facades\Cache;

class ProjectObserver
{
    /**
     * Clear project cache for the tenant.
     */
    protected function clearCache(Project $project): void
    {
        Cache::forget('tenant_' . $project->tenant_id . '_projects');
    }

    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $this->clearCache($project);
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        $this->clearCache($project);
    }

    /**
     * Handle the Project "deleted" event.
     */
    public function deleted(Project $project): void
    {
        $this->clearCache($project);
    }

    /**
     * Handle the Project "restored" event.
     */
    public function restored(Project $project): void
    {
        $this->clearCache($project);
    }
}
