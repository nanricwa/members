<?php

namespace App\Console\Commands;

use App\Models\Newsletter;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledNewsletters extends Command
{
    protected $signature = 'newsletter:send-scheduled';

    protected $description = '予約済みメルマガを配信する';

    public function handle(EmailService $emailService): int
    {
        $newsletters = Newsletter::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($newsletters->isEmpty()) {
            $this->info('No scheduled newsletters to send.');

            return self::SUCCESS;
        }

        foreach ($newsletters as $newsletter) {
            try {
                $emailService->sendNewsletter($newsletter);
                $this->info("Newsletter #{$newsletter->id} '{$newsletter->subject}' dispatched.");
            } catch (\Exception $e) {
                $newsletter->update(['status' => 'failed']);
                Log::error('Scheduled newsletter failed', [
                    'newsletter_id' => $newsletter->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Newsletter #{$newsletter->id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
