<?php

namespace App\Console\Commands;

use App\Enums\MemberPlanStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = '期限切れサブスクリプションのプランを停止する';

    public function handle(): int
    {
        $expiredSubscriptions = Subscription::where('status', SubscriptionStatus::Cancelled)
            ->whereNotNull('current_period_end')
            ->where('current_period_end', '<=', now())
            ->get();

        $count = 0;

        foreach ($expiredSubscriptions as $subscription) {
            try {
                DB::transaction(function () use ($subscription) {
                    // サブスクリプションは既にCancelledなのでそのまま

                    // 対応するMemberPlanを期限切れに
                    $member = $subscription->member;
                    if ($member) {
                        $memberPlan = $member->plans()
                            ->where('plans.id', $subscription->plan_id)
                            ->wherePivot('status', MemberPlanStatus::Active->value)
                            ->first();

                        if ($memberPlan) {
                            $member->plans()->updateExistingPivot($subscription->plan_id, [
                                'status' => MemberPlanStatus::Expired->value,
                                'note' => 'サブスクリプション期限切れにより自動停止',
                            ]);
                        }
                    }
                });

                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to expire subscription', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Subscription {$subscription->id}: {$e->getMessage()}");
            }
        }

        $this->info("Expired {$count} subscriptions.");
        Log::info("ExpireSubscriptions: {$count} subscriptions expired");

        return self::SUCCESS;
    }
}
