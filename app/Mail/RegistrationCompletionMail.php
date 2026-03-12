<?php

namespace App\Mail;

use App\Models\Member;
use App\Models\RegistrationForm;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationCompletionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Member $member,
        public RegistrationForm $form,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->form->completion_email_subject
            ?? '【' . config('app.name') . '】ご登録ありがとうございます';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $bodyText = $this->form->completion_email_body
            ?? 'ご登録が完了しました。下記のリンクからマイページにアクセスしてください。';

        return new Content(
            view: 'emails.registration-completion',
            with: [
                'memberName' => $this->member->name,
                'bodyText' => $bodyText,
                'loginUrl' => route('member.login'),
                'planName' => $this->form->plan?->name,
            ],
        );
    }
}
