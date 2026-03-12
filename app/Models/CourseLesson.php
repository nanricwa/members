<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_module_id',
        'page_id',
        'sort_order',
        'estimated_minutes',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(MemberLessonProgress::class);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function isCompletedBy(Member $member): bool
    {
        return $this->progress()
            ->where('member_id', $member->id)
            ->where('is_completed', true)
            ->exists();
    }

    public function getProgressFor(Member $member): ?MemberLessonProgress
    {
        return $this->progress()
            ->where('member_id', $member->id)
            ->first();
    }
}
