<?php

namespace App\Services\Payment;

use App\Models\Member;
use App\Models\RegistrationForm;
use App\Models\Subscription;

interface PaymentGatewayInterface
{
    /**
     * 一回払いのチェックアウトセッションを作成
     * @return array{checkout_url: string, session_id: string}
     */
    public function createCheckoutSession(
        Member $member,
        RegistrationForm $form,
        int $amount,
        string $successUrl,
        string $cancelUrl,
        string $currency = 'JPY',
    ): array;

    /**
     * 定期課金のチェックアウトセッションを作成
     * @return array{checkout_url: string, session_id: string}
     */
    public function createSubscriptionCheckout(
        Member $member,
        RegistrationForm $form,
        int $amount,
        string $successUrl,
        string $cancelUrl,
        string $currency = 'JPY',
        int $trialDays = 0,
    ): array;

    /**
     * サブスクリプションをキャンセル（期間終了時にキャンセル）
     */
    public function cancelSubscription(Subscription $subscription): bool;

    /**
     * Webhookペイロードを処理し、イベント情報を返す
     * @return array{event_type: string, data: array}
     */
    public function handleWebhook(string $payload, string $signature): array;

    /**
     * Webhook署名を検証
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * ゲートウェイ上の顧客IDを取得または作成
     */
    public function getOrCreateCustomer(Member $member): string;
}
