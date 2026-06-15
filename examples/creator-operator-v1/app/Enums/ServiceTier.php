<?php

namespace App\Enums;

enum ServiceTier: string
{
    case Lite = 'lite';
    case Standard = 'standard';

    public function label(): string
    {
        return match ($this) {
            self::Lite => 'Lite',
            self::Standard => 'Standard',
        };
    }
}
