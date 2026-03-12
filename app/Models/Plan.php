<?php

namespace App\Models;

use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'price',
        'currency',
        'trial_days',
        'duration_days',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => PlanType::class,
            'price' => 'decimal:0',
            'is_active' => 'boolean',
        ];
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'member_plan')
            ->using(MemberPlan::class)
            ->withPivot(['id', 'status', 'started_at', 'expires_at', 'granted_by', 'note'])
            ->withTimestamps();
    }

    public function registrationForms(): HasMany
    {
        return $this->hasMany(RegistrationForm::class);
    }

    public function pages(): BelongsToMany
    {
        return $this->belongsToMany(Page::class, 'page_plan');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_plan');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function isFree(): bool
    {
        return $this->type === PlanType::Free;
    }
}
