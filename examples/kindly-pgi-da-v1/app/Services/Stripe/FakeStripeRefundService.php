<?php

namespace App\Services\Stripe;

use App\Contracts\CreatesStripeRefund;
use App\Models\Payment;
use App\Models\Refund;

class FakeStripeRefundService implements CreatesStripeRefund
{
    public function create(Payment $payment, Refund $refund): string
    {
        return 're_fake_'.$refund->id;
    }
}
