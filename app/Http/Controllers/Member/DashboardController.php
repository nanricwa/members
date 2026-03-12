<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Services\ContentAccessService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(ContentAccessService $accessService)
    {
        $member = Auth::guard('member')->user();
        $categories = $accessService->getAccessibleCategories($member);

        return view('member.dashboard', compact('member', 'categories'));
    }
}
