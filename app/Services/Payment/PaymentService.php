<?php

namespace App\Services\Payment;

use App\Enums\GrantedBy;
use App\Enums\MemberPlanStatus;
use App\Enums\MemberStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Member;
use App\Models\Payment;
use App\Models\RegistrationForm;
use App\Models\Subscription;
use App\Services\EmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * 登録フォームの決済を開始し、チェックアウトURLを返す
     */
    public function initiateFormPayment(Member $member, RegistrationForm $form): string
    {
        $gateway = PaymentGatewayFactory::create($form->payment_gateway);

        // 決済レコードを作成
        $payment = Payment::create([
            'member_id' => $member->id,
            'plan_id' => $form->plan_id,
            'registration_form_id' => $form->id,
            'gateway' => $form->payment_gateway,
            'amount' => $form->amount,
            'currency' => 'JPY',
            'status' => PaymentStatus::Pending,
            'description' => $form->name,
        ]);

        $successUrl = route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('payment.cancel') . '?form=' . $form->slug;

        if ($form->type->value === 'paid_recurring') {
            $result = $gateway->createSubscriptionCheckout(
                member: $member,
                form: $form,
                amount: (int) $form->amount,
                successUrl: $successUrl,
                cancelUrl: $cancelUrl,
                trialDays: $form->trial_days ?? 0,
            );
        } else {
            $result = $gateway->createCheckoutSession(
                member: $member,
                form: $form,
                amount: (int) $form->amount,
                successUrl: $successUrl,
                cancelUrl: $cancelUrl,
            );
        }

        // セッションIDをメタデータに保存
        $payment->update([
            'gateway_payment_id' => $result['session_id'],
            'metadata' => ['checkout_session_id' => $result['session_id']],
        ]);

        return $result['checkout_url'];
    }

    /**
     * 決済完了時の処理（Webhookから呼ばれる）
     */
    public function processCheckoutCompleted(array $sessionData, PaymentGateway $gateway): void
    {
        $sessionId = $sessionData['id'] ?? null;
        $metadata = $sessionData['metadata'] ?? [];

        if (! $sessionId) {
            Log::warning('Webhook: session_id not found in data');

            return;
        }

        // 冪等性チェック: 既に処理済みの場合はスキップ
        $payment = Payment::where('gateway_payment_id', $sessionId)->first();
        if (! $payment) {
            Log::warning("Webhook: Payment not found for session {$sessionId}");

            return;
        }

        if ($payment->isPaid()) {
            Log::info("Webhook: Payment {$payment->id} already completed, skipping");

            return;
        }

        DB::transaction(function () use ($payment, $sessionData, $metadata) {
            // 決済を完了に更新
            $payment->update([
                'status' => PaymentStatus::Completed,
                'paid_at' => now(),
            ]);

            $member = $payment->member;
            $planId = $payment->plan_id;

            if (! $member || ! $planId) {
                return;
            }

            // 会員ステータスをactiveに
            if ($member->status !== MemberStatus::Active) {
                $member->update(['status' => MemberStatus::Active]);
            }

            // プランを付与
            $expiresAt = null;
            $plan = $payment->plan;
            if ($plan && $plan->duration_days) {
                $expiresAt = now()->addDays($plan->duration_days);
            }

            $member->plans()->syncWithoutDetaching([
                $planId => [
                    'status' => MemberPlanStatus::Active->value,
                    'started_at' => now(),
                    'expires_at' => $expiresAt,
                    'granted_by' => GrantedBy::Payment->value,
                    'note' => '決済完了により自動付与',
                ],
            ]);

            // 定期課金の場合、サブスクリプションレコードを作成
            $mode = $sessionData['mode'] ?? null;
            $subscriptionId = $sessionData['subscription'] ?? null;

            if ($mode === 'subscription' && $subscriptionId) {
                Subscription::updateOrCreate(
                    ['gateway_subscription_id' => $subscriptionId],
                    [
                        'member_id' => $member->id,
                        'plan_id' => $planId,
                        'gateway' => $payment->gateway,
                        'status' => SubscriptionStatus::Active,
                        'current_period_start' => now(),
                        'current_period_end' => now()->addMonth(),
                    ]
                );
            }
        });

        // 登録フォーム経由の決済の場合、完了メールを送信
        if ($payment->registration_form_id) {
            $form = $payment->registrationForm;
            $member = $payment->member;
            if ($form && $member) {
                app(EmailService::class)->sendRegistrationCompletion($member, $form);
            }
        }

        Log::info("Webhook: Payment {$payment->id} processed successfully");
    }

    /**
     * 定期課金の更新処理（invoice.paid）
     */
    public function processInvoicePaid(array $invoiceData, PaymentGateway $gateway): void
    {
        $subscriptionId = $invoiceData['subscription'] ?? null;
        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();
        if (! $subscription) {
            return;
        }

        $periodEnd = isset($invoiceData['lines']['data'][0]['period']['end'])
            ? \Carbon\Carbon::createFromTimestamp($invoiceData['lines']['data'][0]['period']['end'])
            : now()->addMonth();

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'current_period_start' => now(),
            'current_period_end' => $periodEnd,
        ]);

        // 決済履歴レコード
        Payment::create([
            'member_id' => $subscription->member_id,
            'plan_id' => $subscription->plan_id,
            'gateway' => $gateway,
            'gateway_payment_id' => $invoiceData['id'] ?? null,
            'amount' => ($invoiceData['amount_paid'] ?? 0),
            'currency' => strtoupper($invoiceData['currency'] ?? 'jpy'),
            'status' => PaymentStatus::Completed,
            'description' => '定期課金更新',
            'paid_at' => now(),
        ]);
    }

    /**
     * サブスクリプション削除（customer.subscription.deleted）
     */
    public function processSubscriptionDeleted(array $subData, PaymentGateway $gateway): void
    {
        $subscriptionId = $subData['id'] ?? null;
        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();
        if (! $subscription) {
            return;
        }

        $subscription->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        // 対応するMemberPlanも期限付きに
        $memberPlan = $subscription->member->plans()
            ->where('plans.id', $subscription->plan_id)
            ->wherePivot('status', 'active')
            ->first();

        if ($memberPlan) {
            $subscription->member->plans()->updateExistingPivot($subscription->plan_id, [
                'expires_at' => $subscription->current_period_end ?? now(),
                'note' => 'サブスクリプションキャンセルにより期限設定',
            ]);
        }
    }

    /**
     * 決済失敗（invoice.payment_failed）
     */
    public function processPaymentFailed(array $invoiceData, PaymentGateway $gateway): void
    {
        $subscriptionId = $invoiceData['subscription'] ?? null;
        if (! $subscriptionId) {
            return;
        }

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();
        if ($subscription) {
            $subscription->update(['status' => SubscriptionStatus::PastDue]);
        }
    }
}
