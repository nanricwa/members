<?php

namespace App\Jobs;

use App\Mail\RegistrationCompletionMail;
use App\Models\EmailLog;
use App\Models\Member;
use App\Models\RegistrationForm;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRegistrationCompletionEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public Member $member,
        public RegistrationForm $form,
    ) {}

    public function handle(): void
    {
        $mailable = new RegistrationCompletionMail($this->member, $this->form);

        $subject = $this->form->completion_email_subject
            ?? '【' . config('app.name') . '】ご登録ありがとうございます';

        $emailLog = EmailLog::create([
            'member_id' => $this->member->id,
            'email_type' => 'registration_complete',
            'subject' => $subject,
            'body_preview' => mb_substr(strip_tags($this->form->completion_email_body ?? ''), 0, 200),
            'status' => 'queued',
            'related_type' => 'registration_form',
            'related_id' => $this->form->id,
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

            Log::error('Registration completion email failed', [
                'member_id' => $this->member->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // リトライのために再throw
        }
    }
}
