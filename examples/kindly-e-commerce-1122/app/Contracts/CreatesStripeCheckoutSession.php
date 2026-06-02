<?php

namespace App\Contracts;

use App\Models\Order;

interface CreatesStripeCheckoutSession
{
    public function createForOrder(Order $order): string;
}
