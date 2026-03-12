<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Download extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'file_path',
        'original_filename',
        'file_size',
        'mime_type',
        'download_limit',
        'total_downloads',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function memberDownloads(): HasMany
    {
        return $this->hasMany(MemberDownload::class);
    }

    public function pageContents(): HasMany
    {
        return $this->hasMany(PageContent::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function hasReachedLimit(): bool
    {
        if ($this->download_limit === null) {
            return false;
        }

        return $this->total_downloads >= $this->download_limit;
    }
}
