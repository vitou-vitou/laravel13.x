<?php

namespace App\Services;

use App\Enums\OperatorPlan;
use App\Models\User;

class OperatorBillingService
{
    public function usesStripe(): bool
    {
        return config('operator-billing.mode') === 'stripe'
            && filled(config('cashier.secret'))
            && filled(config('operator-billing.stripe_prices.pro'));
    }

    public function effectivePlan(User $user): OperatorPlan
    {
        if (! $user->isOperator()) {
            return OperatorPlan::Starter;
        }

        if ($this->usesStripe() && $this->hasActiveProSubscription($user)) {
            return OperatorPlan::Pro;
        }

        return $user->operator_plan ?? OperatorPlan::Starter;
    }

    public function creatorLimit(User $user): int
    {
        return $this->effectivePlan($user)->creatorLimit();
    }

    public function proStripePriceId(): ?string
    {
        $price = config('operator-billing.stripe_prices.pro');

        return is_string($price) && $price !== '' ? $price : null;
    }

    public function hasActiveProSubscription(User $user): bool
    {
        $priceId = $this->proStripePriceId();

        if ($priceId === null) {
            return false;
        }

        return $user->subscribed('default', $priceId);
    }
}
