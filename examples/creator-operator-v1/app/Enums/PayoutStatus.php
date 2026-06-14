<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case Estimated = 'estimated';
    case Confirmed = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::Estimated => 'Estimated',
            self::Confirmed => 'Confirmed',
        };
    }
}
