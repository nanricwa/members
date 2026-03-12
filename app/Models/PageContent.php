<?php

namespace App\Models;

use App\Enums\ContentBlockType;
use App\Enums\VideoProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageContent extends Model
{
    protected $fillable = [
        'page_id',
        'type',
        'body',
        'video_url',
        'video_provider',
        'download_id',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentBlockType::class,
            'video_provider' => VideoProvider::class,
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function download(): BelongsTo
    {
        return $this->belongsTo(Download::class);
    }

    public function getEmbedUrlAttribute(): ?string
    {
        if (!$this->video_url) {
            return null;
        }

        return match($this->video_provider) {
            VideoProvider::YouTube => $this->getYouTubeEmbedUrl(),
            VideoProvider::Vimeo => $this->getVimeoEmbedUrl(),
            default => null,
        };
    }

    private function getYouTubeEmbedUrl(): ?string
    {
        $videoId = null;

        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $this->video_url, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId ? "https://www.youtube.com/embed/{$videoId}" : null;
    }

    private function getVimeoEmbedUrl(): ?string
    {
        $videoId = null;

        if (preg_match('/vimeo\.com\/(\d+)/', $this->video_url, $matches)) {
            $videoId = $matches[1];
        }

        return $videoId ? "https://player.vimeo.com/video/{$videoId}" : null;
    }
}
