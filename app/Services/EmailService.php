<?php

namespace App\Services;

use App\Jobs\SendAutomationEmail;
use App\Jobs\SendNewsletterToMember;
use App\Jobs\SendRegistrationCompletionEmail;
use App\Models\AutomationTask;
use App\Models\Member;
use App\Models\Newsletter;
use App\Models\RegistrationForm;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * 登録完了メールを送信（キュー経由）
     */
    public function sendRegistrationCompletion(Member $member, RegistrationForm $form): void
    {
        // 完了メールが設定されていない場合はスキップ
        if (empty($form->completion_email_subject) && empty($form->completion_email_body)) {
            return;
        }

        SendRegistrationCompletionEmail::dispatch($member, $form);

        Log::info('Registration completion email queued', [
            'member_id' => $member->id,
            'form_id' => $form->id,
        ]);
    }

    /**
     * メルマガを対象メンバーに配信開始
     */
    public function sendNewsletter(Newsletter $newsletter): void
    {
        $members = $newsletter->getTargetMembersQuery()->get();

        $newsletter->update([
            'status' => 'sending',
            'total_recipients' => $members->count(),
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        foreach ($members as $member) {
            SendNewsletterToMember::dispatch($member, $newsletter);
        }

        Log::info('Newsletter dispatch started', [
            'newsletter_id' => $newsletter->id,
            'total_recipients' => $members->count(),
        ]);
    }

    /**
     * 自動タスクメールを送信（キュー経由）
     */
    public function sendAutomationEmail(
        Member $member,
        string $subject,
        string $bodyHtml,
        ?AutomationTask $task = null,
    ): void {
        SendAutomationEmail::dispatch($member, $subject, $bodyHtml, $task);
    }
}
