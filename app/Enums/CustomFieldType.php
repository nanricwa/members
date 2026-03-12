<?php

namespace App\Enums;

enum CustomFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Select = 'select';
    case Checkbox = 'checkbox';
    case Radio = 'radio';
    case Date = 'date';
    case Number = 'number';

    public function label(): string
    {
        return match($this) {
            self::Text => 'テキスト',
            self::Textarea => 'テキストエリア',
            self::Select => 'セレクト',
            self::Checkbox => 'チェックボックス',
            self::Radio => 'ラジオ',
            self::Date => '日付',
            self::Number => '数値',
        };
    }
}
