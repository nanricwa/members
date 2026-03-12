<?php

namespace App\Models;

use App\Enums\MenuItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'label',
        'type',
        'target_id',
        'url',
        'icon',
        'parent_id',
        'sort_order',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'type' => MenuItemType::class,
            'is_visible' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function getResolvedUrlAttribute(): ?string
    {
        return match($this->type) {
            MenuItemType::Category => $this->target_id
                ? route('member.category', Category::find($this->target_id)?->slug ?? '')
                : null,
            MenuItemType::Page => $this->target_id
                ? route('member.page', Page::find($this->target_id)?->slug ?? '')
                : null,
            MenuItemType::Url => $this->url,
            MenuItemType::Divider => null,
        };
    }
}
