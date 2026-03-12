<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends Model
{
    protected $fillable = [
        'automation_task_id',
        'member_id',
        'action_type',
        'action_detail',
        'status',
        'error_message',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'action_detail' => 'array',
            'executed_at' => 'datetime',
        ];
    }

    public function automationTask(): BelongsTo
    {
        return $this->belongsTo(AutomationTask::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
