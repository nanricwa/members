<?php

namespace App\Services\Payment;

use App\Models\Member;
use App\Models\RegistrationForm;
use App\Models\Subscription;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;
use Stripe\Webhook;

class StripePaymentGateway implements PaymentGatewayInterface
{
    public function __construct(
        private string $secretKey,
        private string $webhookSecret,
    ) {
        Stripe::setApiKey($this->secretKey);
    }

    public function createCheckoutSession(
        Member $member,
        RegistrationForm $form,
        int $amount,
        string $successUrl,
        string $cancelUrl,
        string $currency = 'JPY',
    ): array {
        $customerId = $this->getOrCreateCustomer($member);

        $session = StripeSession::create([
            'customer' => $customerId,
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($currency),
                    'product_data' => [
                        'name' => $form->name,
                        'description' => $form->plan?->name ?? '',
                    ],
                    'unit_amount' => $amount, // JPYは小数なしなのでそのまま
                ],
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'member_id' => $member->id,
                'form_id' => $form->id,
                'plan_id' => $form->plan_id,
                'type' => 'one_time',
            ],
        ]);

        return [
            'checkout_url' => $session->url,
            'session_id' => $session->id,
        ];
    }

    public function createSubscriptionCheckout(
        Member $member,
        RegistrationForm $form,
        int $amount,
        string $successUrl,
        string $cancelUrl,
        string $currency = 'JPY',
        int $trialDays = 0,
    ): array {
        $customerId = $this->getOrCreateCustomer($member);

        $params = [
            'customer' => $customerId,
            'mode' => 'subscription',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($currency),
                    'product_data' => [
                        'name' => $form->name,
                        'description' => $form->plan?->name ?? '',
                    ],
                    'unit_amount' => $amount,
                    'recurring' => [
                        'interval' => 'month',
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'member_id' => $member->id,
                'form_id' => $form->id,
                'plan_id' => $form->plan_id,
                'type' => 'recurring',
            ],
        ];

        if ($trialDays > 0) {
            $params['subscription_data'] = [
                'trial_period_days' => $trialDays,
                'metadata' => [
                    'member_id' => $member->id,
                    'form_id' => $form->id,
                    'plan_id' => $form->plan_id,
                ],
            ];
        }

        $session = StripeSession::create($params);

        return [
            'checkout_url' => $session->url,
            'session_id' => $session->id,
        ];
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        try {
            StripeSubscription::update($subscription->gateway_subscription_id, [
                'cancel_at_period_end' => true,
            ]);

            return true;
        } catch (\Exception $e) {
            report($e);

            return false;
        }
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        $event = Webhook::constructEvent($payload, $signature, $this->webhookSecret);

        return [
            'event_type' => $event->type,
            'data' => $event->data->object->toArray(),
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            Webhook::constructEvent($payload, $signature, $this->webhookSecret);

            return true;
        } catch (\Exception) {
            return false;
        }
    }

    public function getOrCreateCustomer(Member $member): string
    {
        if ($member->stripe_customer_id) {
            return $member->stripe_customer_id;
        }

        $customer = Customer::create([
            'email' => $member->email,
            'name' => $member->name,
            'metadata' => [
                'member_id' => $member->id,
            ],
        ]);

        $member->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }
}
