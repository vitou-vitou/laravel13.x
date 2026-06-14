<?php

namespace App\Enums;

enum SettlementPlatform: string
{
    case Youtube = 'youtube';
    case Instagram = 'instagram';
    case Facebook = 'facebook';

    public function label(): string
    {
        return match ($this) {
            self::Youtube => 'YouTube',
            self::Instagram => 'Instagram',
            self::Facebook => 'Facebook',
        };
    }
}
