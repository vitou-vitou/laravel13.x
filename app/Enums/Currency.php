<?php

namespace App\Enums;

enum Currency: string
{
    case KHR = 'KHR';
    case USD = 'USD';

    /**
     * Number of minor units per major unit for this currency.
     *
     * KHR is conventionally handled without decimal subunits; USD has cents.
     */
    public function minorUnitScale(): int
    {
        return match ($this) {
            self::KHR => 100,
            self::USD => 100,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $currency) => $currency->value, self::cases());
    }
}
