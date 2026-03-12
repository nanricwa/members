<?php

namespace App\Enums;

enum PlanType: string
{
    case Free = 'free';
    case PaidOnce = 'paid_once';
    case PaidRecurring = 'paid_recurring';

    public function label(): string
    {
        return match($this) {
            self::Free => '無料',
            self::PaidOnce => '有料（一回払い）',
            self::PaidRecurring => '有料（定期課金）',
        };
    }
}
