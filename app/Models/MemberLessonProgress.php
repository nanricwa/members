<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberLessonProgress extends Model
{
    use HasFactory;

    protected $table = 'member_lesson_progress';

    protected $fillable = [
        'member_id',
        'course_lesson_id',
        'is_completed',
        'completed_at',
        'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function courseLesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class);
    }
}
