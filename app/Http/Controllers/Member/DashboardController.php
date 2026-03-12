<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\ContentAccessService;
use App\Services\CourseProgressService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(ContentAccessService $accessService, CourseProgressService $progressService)
    {
        $member = Auth::guard('member')->user();
        $categories = $accessService->getAccessibleCategories($member);
        $courses = $accessService->getAccessibleCourses($member);
        $courses = $progressService->getCoursesWithProgress($member, $courses);

        return view('member.dashboard', compact('member', 'categories', 'courses'));
    }
}
