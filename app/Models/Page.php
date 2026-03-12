<?php

namespace App\Models;

use App\Enums\PageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'type',
        'is_published',
        'published_at',
        'unpublished_at',
        'meta_title',
        'meta_description',
        'thumbnail',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => PageType::class,
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'unpublished_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(PageContent::class)->orderBy('sort_order');
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'page_plan');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('unpublished_at')
                    ->orWhere('unpublished_at', '>', now());
            });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function isCurrentlyPublished(): bool
    {
        if (!$this->is_published) {
            return false;
        }

        $now = now();

        if ($this->published_at && $now->lt($this->published_at)) {
            return false;
        }

        if ($this->unpublished_at && $now->gte($this->unpublished_at)) {
            return false;
        }

        return true;
    }
}
