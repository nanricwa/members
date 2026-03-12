<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Services\ContentAccessService;
use App\Services\CourseProgressService;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    public function __construct(
        protected ContentAccessService $accessService,
        protected CourseProgressService $progressService,
    ) {}

    /**
     * コース一覧
     */
    public function index()
    {
        $member = Auth::guard('member')->user();
        $courses = $this->accessService->getAccessibleCourses($member);
        $courses = $this->progressService->getCoursesWithProgress($member, $courses);

        return view('member.courses.index', compact('member', 'courses'));
    }

    /**
     * コース詳細（シラバス）
     */
    public function show(Course $course)
    {
        $member = Auth::guard('member')->user();

        if (!$this->accessService->canAccessCourse($member, $course)) {
            abort(403, 'このコースへのアクセス権がありません。');
        }

        $course->load(['modules.lessons.page']);
        $progressMap = $this->progressService->getCourseProgressMap($member, $course);
        $progress = $this->progressService->getCourseProgress($member, $course);
        $nextLesson = $this->progressService->getNextLesson($member, $course);

        return view('member.courses.show', compact(
            'course', 'member', 'progressMap', 'progress', 'nextLesson'
        ));
    }

    /**
     * レッスン表示（コース文脈内）
     */
    public function lesson(Course $course, CourseLesson $lesson)
    {
        $member = Auth::guard('member')->user();

        if (!$this->accessService->canAccessCourse($member, $course)) {
            abort(403, 'このコースへのアクセス権がありません。');
        }

        $lesson->load('page.contents.download', 'module');

        // アクセス記録
        $this->progressService->recordAccess($member, $lesson);

        // 前後レッスン取得（HasManyThroughは既にcourse_modulesをjoinするためテーブル指定で曖昧さ回避）
        $allLessons = CourseLesson::query()
            ->join('course_modules', 'course_lessons.course_module_id', '=', 'course_modules.id')
            ->where('course_modules.course_id', $course->id)
            ->orderBy('course_modules.sort_order')
            ->orderBy('course_lessons.sort_order')
            ->select('course_lessons.*')
            ->get();

        $currentIndex = $allLessons->search(fn ($l) => $l->id === $lesson->id);
        $prevLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;

        $isCompleted = $lesson->isCompletedBy($member);

        return view('member.courses.lesson', compact(
            'course', 'lesson', 'member', 'prevLesson', 'nextLesson', 'isCompleted'
        ));
    }

    /**
     * レッスン完了/未完了トグル
     */
    public function toggleComplete(Course $course, CourseLesson $lesson)
    {
        $member = Auth::guard('member')->user();

        if (!$this->accessService->canAccessCourse($member, $course)) {
            abort(403);
        }

        $progressRecord = $lesson->getProgressFor($member);

        if ($progressRecord && $progressRecord->is_completed) {
            $this->progressService->markIncomplete($member, $lesson);
            $message = 'レッスンを未完了に戻しました。';
        } else {
            $this->progressService->markCompleted($member, $lesson);
            $message = 'レッスンを完了しました！';
        }

        return back()->with('success', $message);
    }
}
