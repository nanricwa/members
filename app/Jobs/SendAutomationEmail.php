<?php

namespace App\Jobs;

use App\Mail\AutomationMail;
use App\Models\AutomationTask;
use App\Models\EmailLog;
use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAutomationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Member $member,
        public string $subject,
        public string $bodyHtml,
        public ?AutomationTask $task = null,
    ) {}

    public function handle(): void
    {
        $mailable = new AutomationMail($this->subject, $this->bodyHtml);

        $emailLog = EmailLog::create([
            'member_id' => $this->member->id,
            'email_type' => 'automation',
            'subject' => $this->subject,
            'body_preview' => mb_substr(strip_tags($this->bodyHtml), 0, 200),
            'status' => 'queued',
            'related_type' => $this->task ? 'automation_task' : null,
            'related_id' => $this->task?->id,
        ]);

        try {
            Mail::to($this->member->email)->send($mailable);

            $emailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $emailLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Automation email failed', [
                'member_id' => $this->member->id,
                'task_id' => $this->task?->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
