<?php

namespace App\Enums;

enum OperatorPlan: string
{
    case Starter = 'starter';
    case Pro = 'pro';

    public function label(): string
    {
        return match ($this) {
            self::Starter => 'Starter',
            self::Pro => 'Pro',
        };
    }

    public function creatorLimit(): int
    {
        return (int) config('operator-billing.plans.'.$this->value.'.creator_limit', 3);
    }
}
