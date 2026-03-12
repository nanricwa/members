<?php

namespace App\Enums;

enum FormType: string
{
    case Free = 'free';
    case PaidOnce = 'paid_once';
    case PaidRecurring = 'paid_recurring';
    case Upgrade = 'upgrade';
    case Additional = 'additional';

    public function label(): string
    {
        return match($this) {
            self::Free => '無料登録',
            self::PaidOnce => '有料（一回払い）',
            self::PaidRecurring => '有料（定期課金）',
            self::Upgrade => 'アップグレード',
            self::Additional => '追加申請',
        };
    }
}
