<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'thumbnail',
        'estimated_minutes',
        'is_published',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function modules(): HasMany
    {
        return $this->hasMany(CourseModule::class)->orderBy('sort_order');
    }

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class, 'course_plan');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(CourseLesson::class, CourseModule::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function getTotalLessonsAttribute(): int
    {
        return $this->lessons()->count();
    }
}
