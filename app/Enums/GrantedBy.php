<?php

namespace App\Enums;

enum GrantedBy: string
{
    case Registration = 'registration';
    case Admin = 'admin';
    case Task = 'task';
    case Payment = 'payment';

    public function label(): string
    {
        return match($this) {
            self::Registration => '登録時',
            self::Admin => '管理者操作',
            self::Task => '自動タスク',
            self::Payment => '決済',
        };
    }
}
