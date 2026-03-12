<?php

namespace App\Services\Payment;

use App\Enums\PaymentGateway;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public static function create(PaymentGateway $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            PaymentGateway::Stripe => new StripePaymentGateway(
                config('services.stripe.secret'),
                config('services.stripe.webhook_secret'),
            ),
            // Phase 3で追加予定:
            // PaymentGateway::Square => new SquarePaymentGateway(...),
            // PaymentGateway::UnivaPay => new UnivaPayPaymentGateway(...),
            default => throw new InvalidArgumentException("未対応の決済ゲートウェイ: {$gateway->value}"),
        };
    }
}
