<?php

namespace App\Models;

use App\Enums\GrantedBy;
use App\Enums\MemberPlanStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MemberPlan extends Pivot
{
    protected $table = 'member_plan';

    public $incrementing = true;

    protected $fillable = [
        'member_id',
        'plan_id',
        'status',
        'started_at',
        'expires_at',
        'granted_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'status' => MemberPlanStatus::class,
            'granted_by' => GrantedBy::class,
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === MemberPlanStatus::Active;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
