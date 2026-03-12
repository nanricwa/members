<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationTask extends Model
{
    protected $fillable = [
        'name',
        'description',
        'trigger_type',
        'trigger_value',
        'action_type',
        'action_value',
        'is_active',
        'last_executed_at',
    ];

    protected function casts(): array
    {
        return [
            'trigger_value' => 'array',
            'action_value' => 'array',
            'is_active' => 'boolean',
            'last_executed_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AutomationLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
