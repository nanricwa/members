<?php

namespace App\Enums;

enum MenuItemType: string
{
    case Category = 'category';
    case Page = 'page';
    case Url = 'url';
    case Divider = 'divider';

    public function label(): string
    {
        return match($this) {
            self::Category => 'カテゴリ',
            self::Page => 'ページ',
            self::Url => 'URL',
            self::Divider => '区切り線',
        };
    }
}
