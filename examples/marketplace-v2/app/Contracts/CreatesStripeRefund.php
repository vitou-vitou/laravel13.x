<?php

namespace App\Contracts;

use App\Models\Payment;
use App\Models\Refund;

interface CreatesStripeRefund
{
    public function create(Payment $payment, Refund $refund): string;
}
