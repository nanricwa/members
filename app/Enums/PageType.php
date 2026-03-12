<?php

namespace App\Enums;

enum PageType: string
{
    case Text = 'text';
    case Video = 'video';
    case Download = 'download';
    case Link = 'link';
    case Html = 'html';

    public function label(): string
    {
        return match($this) {
            self::Text => 'テキスト',
            self::Video => '動画',
            self::Download => 'ダウンロード',
            self::Link => 'リンク',
            self::Html => 'HTML',
        };
    }
}
