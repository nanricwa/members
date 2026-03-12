<?php

namespace App\Enums;

enum VideoProvider: string
{
    case YouTube = 'youtube';
    case Vimeo = 'vimeo';

    public function label(): string
    {
        return match($this) {
            self::YouTube => 'YouTube',
            self::Vimeo => 'Vimeo',
        };
    }
}
