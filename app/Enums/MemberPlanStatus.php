<?php

namespace App\Enums;

enum MemberPlanStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Active => '有効',
            self::Suspended => '一時停止',
            self::Expired => '期限切れ',
            self::Cancelled => 'キャンセル',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'success',
            self::Suspended => 'warning',
            self::Expired => 'danger',
            self::Cancelled => 'gray',
        };
    }
}
