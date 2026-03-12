<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Member;
use App\Models\MemberLessonProgress;
use Illuminate\Support\Collection;

class CourseProgressService
{
    /**
     * レッスンアクセスを記録
     */
    public function recordAccess(Member $member, CourseLesson $lesson): MemberLessonProgress
    {
        return MemberLessonProgress::updateOrCreate(
            [
                'member_id' => $member->id,
                'course_lesson_id' => $lesson->id,
            ],
            [
                'last_accessed_at' => now(),
            ]
        );
    }

    /**
     * レッスンを完了にする
     */
    public function markCompleted(Member $member, CourseLesson $lesson): MemberLessonProgress
    {
        return MemberLessonProgress::updateOrCreate(
            [
                'member_id' => $member->id,
                'course_lesson_id' => $lesson->id,
            ],
            [
                'is_completed' => true,
                'completed_at' => now(),
                'last_accessed_at' => now(),
            ]
        );
    }

    /**
     * レッスンを未完了に戻す
     */
    public function markIncomplete(Member $member, CourseLesson $lesson): MemberLessonProgress
    {
        return MemberLessonProgress::updateOrCreate(
            [
                'member_id' => $member->id,
                'course_lesson_id' => $lesson->id,
            ],
            [
                'is_completed' => false,
                'completed_at' => null,
            ]
        );
    }

    /**
     * コースの進捗率を取得（0-100）
     */
    public function getCourseProgress(Member $member, Course $course): int
    {
        $lessonIds = CourseLesson::query()
            ->join('course_modules', 'course_lessons.course_module_id', '=', 'course_modules.id')
            ->where('course_modules.course_id', $course->id)
            ->pluck('course_lessons.id');

        $totalLessons = $lessonIds->count();
        if ($totalLessons === 0) {
            return 0;
        }

        $completedLessons = MemberLessonProgress::where('member_id', $member->id)
            ->where('is_completed', true)
            ->whereIn('course_lesson_id', $lessonIds)
            ->count();

        return (int) round(($completedLessons / $totalLessons) * 100);
    }

    /**
     * コースの進捗マップを取得（course_lesson_id => MemberLessonProgress）
     */
    public function getCourseProgressMap(Member $member, Course $course): Collection
    {
        $lessonIds = CourseLesson::query()
            ->join('course_modules', 'course_lessons.course_module_id', '=', 'course_modules.id')
            ->where('course_modules.course_id', $course->id)
            ->pluck('course_lessons.id');

        return MemberLessonProgress::where('member_id', $member->id)
            ->whereIn('course_lesson_id', $lessonIds)
            ->get()
            ->keyBy('course_lesson_id');
    }

    /**
     * 次の未完了レッスンを取得
     */
    public function getNextLesson(Member $member, Course $course): ?CourseLesson
    {
        $lessonIds = CourseLesson::query()
            ->join('course_modules', 'course_lessons.course_module_id', '=', 'course_modules.id')
            ->where('course_modules.course_id', $course->id)
            ->pluck('course_lessons.id');

        $completedLessonIds = MemberLessonProgress::where('member_id', $member->id)
            ->where('is_completed', true)
            ->whereIn('course_lesson_id', $lessonIds)
            ->pluck('course_lesson_id');

        return CourseLesson::query()
            ->join('course_modules', 'course_lessons.course_module_id', '=', 'course_modules.id')
            ->where('course_modules.course_id', $course->id)
            ->whereNotIn('course_lessons.id', $completedLessonIds)
            ->orderBy('course_modules.sort_order')
            ->orderBy('course_lessons.sort_order')
            ->select('course_lessons.*')
            ->first();
    }

    /**
     * コース一覧に進捗率を付与
     */
    public function getCoursesWithProgress(Member $member, Collection $courses): Collection
    {
        // 全コースのlesson IDをまとめて取得（N+1回避）
        $courseIds = $courses->pluck('id');
        $lessonsByCourse = CourseLesson::whereHas('module', function ($q) use ($courseIds) {
            $q->whereIn('course_id', $courseIds);
        })->with('module:id,course_id')->get()->groupBy(fn ($l) => $l->module->course_id);

        // メンバーの全進捗を一括取得
        $allLessonIds = $lessonsByCourse->flatten()->pluck('id');
        $completedMap = MemberLessonProgress::where('member_id', $member->id)
            ->where('is_completed', true)
            ->whereIn('course_lesson_id', $allLessonIds)
            ->pluck('course_lesson_id')
            ->flip();

        return $courses->map(function ($course) use ($lessonsByCourse, $completedMap) {
            $lessons = $lessonsByCourse->get($course->id, collect());
            $total = $lessons->count();
            $completed = $lessons->filter(fn ($l) => $completedMap->has($l->id))->count();

            $course->progress_percentage = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
            $course->total_lessons_count = $total;
            $course->completed_lessons_count = $completed;

            return $course;
        });
    }
}
