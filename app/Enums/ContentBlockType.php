<?php

namespace App\Enums;

enum ContentBlockType: string
{
    case Text = 'text';
    case Video = 'video';
    case Download = 'download';
    case Heading = 'heading';
    case Image = 'image';
    case Html = 'html';
    case Divider = 'divider';

    public function label(): string
    {
        return match($this) {
            self::Text => 'テキスト',
            self::Video => '動画',
            self::Download => 'ダウンロード',
            self::Heading => '見出し',
            self::Image => '画像',
            self::Html => 'HTML',
            self::Divider => '区切り線',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Text => 'heroicon-o-document-text',
            self::Video => 'heroicon-o-play-circle',
            self::Download => 'heroicon-o-arrow-down-tray',
            self::Heading => 'heroicon-o-hashtag',
            self::Image => 'heroicon-o-photo',
            self::Html => 'heroicon-o-code-bracket',
            self::Divider => 'heroicon-o-minus',
        };
    }
}
