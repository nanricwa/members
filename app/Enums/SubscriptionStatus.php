<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Paused = 'paused';

    public function label(): string
    {
        return match($this) {
            self::Active => '有効',
            self::PastDue => '支払い遅延',
            self::Cancelled => 'キャンセル',
            self::Paused => '一時停止',
        };
    }
}
