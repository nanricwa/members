<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Course;
use App\Models\Download;
use App\Models\Member;
use App\Models\MemberDownload;
use App\Models\Page;
use Illuminate\Support\Collection;

class ContentAccessService
{
    public function canAccessPage(Member $member, Page $page): bool
    {
        if (!$page->isCurrentlyPublished()) {
            return false;
        }

        $pagePlanIds = $page->plans()->pluck('plans.id')->toArray();

        // ページにプラン制限がない場合、カテゴリの制限を確認
        if (empty($pagePlanIds) && $page->category) {
            return $this->canAccessCategory($member, $page->category);
        }

        // プラン制限がない場合はアクセス可能
        if (empty($pagePlanIds)) {
            return true;
        }

        return $member->hasAnyActivePlan($pagePlanIds);
    }

    public function canAccessCategory(Member $member, Category $category): bool
    {
        if (!$category->is_published) {
            return false;
        }

        $categoryPlanIds = $category->plans()->pluck('plans.id')->toArray();

        // プラン制限がない場合はアクセス可能
        if (empty($categoryPlanIds)) {
            return true;
        }

        return $member->hasAnyActivePlan($categoryPlanIds);
    }

    public function canDownload(Member $member, Download $download): bool
    {
        if (!$download->is_active) {
            return false;
        }

        if ($download->hasReachedLimit()) {
            return false;
        }

        return true;
    }

    public function getAccessibleCategories(Member $member): Collection
    {
        return Category::published()->ordered()->get()
            ->filter(fn (Category $category) => $this->canAccessCategory($member, $category));
    }

    public function getAccessiblePages(Member $member, ?Category $category = null): Collection
    {
        $query = Page::published()->ordered();

        if ($category) {
            $query->where('category_id', $category->id);
        }

        return $query->get()
            ->filter(fn (Page $page) => $this->canAccessPage($member, $page));
    }

    public function canAccessCourse(Member $member, Course $course): bool
    {
        if (!$course->is_published) {
            return false;
        }

        $coursePlanIds = $course->plans()->pluck('plans.id')->toArray();

        // プラン制限がない場合はアクセス可能
        if (empty($coursePlanIds)) {
            return true;
        }

        return $member->hasAnyActivePlan($coursePlanIds);
    }

    public function getAccessibleCourses(Member $member): Collection
    {
        return Course::published()->ordered()->get()
            ->filter(fn (Course $course) => $this->canAccessCourse($member, $course));
    }
}
