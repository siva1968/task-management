<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    /**
     * Clear user cache for the tenant.
     */
    protected function clearCache(User $user): void
    {
        if ($user->tenant_id) {
            Cache::forget('tenant_' . $user->tenant_id . '_users');
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->clearCache($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->clearCache($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->clearCache($user);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $this->clearCache($user);
    }
}
