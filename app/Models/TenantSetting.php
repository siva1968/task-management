<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
        'type',
    ];

    /**
     * Get the tenant that owns the setting.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the value cast to the appropriate type.
     */
    public function getCastedValue()
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }
}
