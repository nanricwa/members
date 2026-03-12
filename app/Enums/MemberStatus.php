<?php

namespace App\Enums;

enum MemberStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Withdrawn = 'withdrawn';
    case Pending = 'pending';

    public function label(): string
    {
        return match($this) {
            self::Active => 'アクティブ',
            self::Suspended => '一時停止',
            self::Withdrawn => '退会',
            self::Pending => '保留',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'success',
            self::Suspended => 'warning',
            self::Withdrawn => 'danger',
            self::Pending => 'gray',
        };
    }
}
