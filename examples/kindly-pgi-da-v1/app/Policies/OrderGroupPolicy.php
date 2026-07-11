<?php

namespace App\Policies;

use App\Models\OrderGroup;
use App\Models\User;

class OrderGroupPolicy
{
    public function view(User $user, OrderGroup $orderGroup): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isVendor()) {
            return $user->vendor?->id === $orderGroup->vendor_id;
        }

        return $orderGroup->order->user_id === $user->id;
    }

    public function update(User $user, OrderGroup $orderGroup): bool
    {
        return $user->isVendor()
            && $user->vendor?->id === $orderGroup->vendor_id;
    }
}
