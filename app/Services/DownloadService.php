<?php

namespace App\Services;

use App\Models\Download;
use App\Models\Member;
use App\Models\MemberDownload;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadService
{
    public function download(Member $member, Download $download): StreamedResponse
    {
        // DL記録
        MemberDownload::create([
            'member_id' => $member->id,
            'download_id' => $download->id,
            'downloaded_at' => now(),
            'ip_address' => request()->ip(),
        ]);

        // DL数カウントアップ
        $download->increment('total_downloads');

        $filename = $download->original_filename ?: basename($download->file_path);

        return Storage::disk('local')->download($download->file_path, $filename);
    }
}
