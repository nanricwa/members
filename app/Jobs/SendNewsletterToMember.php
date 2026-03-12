<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\EmailLog;
use App\Models\Member;
use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNewsletterToMember implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Member $member,
        public Newsletter $newsletter,
    ) {}

    public function handle(): void
    {
        $mailable = new NewsletterMail($this->newsletter);

        $emailLog = EmailLog::create([
            'member_id' => $this->member->id,
            'email_type' => 'newsletter',
            'subject' => $this->newsletter->subject,
            'body_preview' => mb_substr(strip_tags($this->newsletter->body_html), 0, 200),
            'status' => 'queued',
            'related_type' => 'newsletter',
            'related_id' => $this->newsletter->id,
        ]);

        try {
            Mail::to($this->member->email)->send($mailable);

            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            // sent_count をインクリメント
            DB::table('newsletters')
                ->where('id', $this->newsletter->id)
                ->increment('sent_count');

        } catch (\Exception $e) {
            $emailLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // failed_count をインクリメント
            DB::table('newsletters')
                ->where('id', $this->newsletter->id)
                ->increment('failed_count');

            Log::error('Newsletter email failed', [
                'member_id' => $this->member->id,
                'newsletter_id' => $this->newsletter->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
