<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = [
        'subject',
        'body_html',
        'status',
        'target_type',
        'target_value',
        'scheduled_at',
        'sent_at',
        'total_recipients',
        'sent_count',
        'failed_count',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'array',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * 配信対象の会員クエリを返す
     */
    public function getTargetMembersQuery(): Builder
    {
        $query = Member::where('status', 'active');

        return match ($this->target_type) {
            'plan' => $query->whereHas('plans', function ($q) {
                $q->whereIn('plans.id', $this->target_value['plan_ids'] ?? [])
                    ->wherePivot('status', 'active');
            }),
            'status' => $query->whereIn('status', $this->target_value['statuses'] ?? []),
            default => $query, // 'all'
        };
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isSending(): bool
    {
        return $this->status === 'sending';
    }
}
