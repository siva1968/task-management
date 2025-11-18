<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'subdomain',
        'domain',
        'subscription_tier',
        'feature_flags',
        'status',
        'trial_ends_at',
        'notes',
    ];

    protected $casts = [
        'feature_flags' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Get the users for this tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the projects for this tenant.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Get the tasks for this tenant.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the settings for this tenant.
     */
    public function settings()
    {
        return $this->hasMany(TenantSetting::class);
    }

    /**
     * Check if tenant is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if tenant is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
}
