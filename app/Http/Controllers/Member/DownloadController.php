<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Download;
use App\Services\ContentAccessService;
use App\Services\DownloadService;
use Illuminate\Support\Facades\Auth;

class DownloadController extends Controller
{
    public function __invoke(
        Download $download,
        ContentAccessService $accessService,
        DownloadService $downloadService
    ) {
        $member = Auth::guard('member')->user();

        if (!$accessService->canDownload($member, $download)) {
            abort(403, 'このファイルをダウンロードできません。');
        }

        return $downloadService->download($member, $download);
    }
}
