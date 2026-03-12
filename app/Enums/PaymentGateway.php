<?php

namespace App\Enums;

enum PaymentGateway: string
{
    case None = 'none';
    case Stripe = 'stripe';
    case Square = 'square';
    case UnivaPay = 'univapay';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match($this) {
            self::None => 'なし',
            self::Stripe => 'Stripe',
            self::Square => 'Square',
            self::UnivaPay => 'UnivaPay',
            self::BankTransfer => '銀行振込',
        };
    }
}
