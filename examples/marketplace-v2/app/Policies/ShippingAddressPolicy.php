<?php

namespace App\Policies;

use App\Models\ShippingAddress;
use App\Models\User;

class ShippingAddressPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function update(User $user, ShippingAddress $shippingAddress): bool
    {
        return $shippingAddress->user_id === $user->id;
    }

    public function delete(User $user, ShippingAddress $shippingAddress): bool
    {
        return $shippingAddress->user_id === $user->id;
    }
}
