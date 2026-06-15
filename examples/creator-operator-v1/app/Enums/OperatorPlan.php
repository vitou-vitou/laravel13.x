<?php

namespace App\Enums;

enum OperatorPlan: string
{
    case Starter = 'starter';
    case Pro = 'pro';
    case Demo = 'demo';

    public function label(): string
    {
        return match ($this) {
            self::Starter => 'Starter',
            self::Pro => 'Pro',
            self::Demo => 'Demo',
        };
    }

    public function creatorLimit(): int
    {
        return (int) config('operator-billing.plans.'.$this->value.'.creator_limit', 3);
    }
}
