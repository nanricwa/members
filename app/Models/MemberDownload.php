<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberDownload extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'download_id',
        'downloaded_at',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function download(): BelongsTo
    {
        return $this->belongsTo(Download::class);
    }
}
