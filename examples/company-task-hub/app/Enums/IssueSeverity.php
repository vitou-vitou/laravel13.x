<?php

namespace App\Enums;

enum IssueSeverity: string
{
    case Minor = 'minor';
    case Major = 'major';
    case Critical = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::Minor => 'Minor',
            self::Major => 'Major',
            self::Critical => 'Critical',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Minor => 'info',
            self::Major => 'warning',
            self::Critical => 'danger',
        };
    }
}
