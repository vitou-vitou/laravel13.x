<?php

namespace App\Enums;

enum Role: string
{
    case Policyholder = 'policyholder';
    case Adjuster = 'adjuster';
    case Finance = 'finance';
    case InsurerAdmin = 'insurer_admin';

    /**
     * All role values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (self $role) => $role->value, self::cases());
    }

    /**
     * Roles that operate on the insurer side (tenant-scoped staff).
     *
     * @return array<int, self>
     */
    public static function staff(): array
    {
        return [self::Adjuster, self::Finance, self::InsurerAdmin];
    }

    public function isStaff(): bool
    {
        return in_array($this, self::staff(), true);
    }
}
