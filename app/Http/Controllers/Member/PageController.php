<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Services\ContentAccessService;
use Illuminate\Support\Facades\Auth;

class PageController extends Controller
{
    public function __construct(
        protected ContentAccessService $accessService
    ) {}

    public function showCategory(Category $category)
    {
        $member = Auth::guard('member')->user();

        if (!$this->accessService->canAccessCategory($member, $category)) {
            abort(403, 'このカテゴリへのアクセス権がありません。');
        }

        $pages = $this->accessService->getAccessiblePages($member, $category);

        return view('member.categories.show', compact('category', 'pages', 'member'));
    }

    public function showPage(Page $page)
    {
        $member = Auth::guard('member')->user();

        if (!$this->accessService->canAccessPage($member, $page)) {
            abort(403, 'このページへのアクセス権がありません。');
        }

        $page->load('contents.download');

        return view('member.pages.show', compact('page', 'member'));
    }
}
