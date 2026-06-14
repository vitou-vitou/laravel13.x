<?php

namespace App\Contracts;

use App\Models\Payout;
use App\Models\Vendor;

interface CreatesStripeConnectTransfer
{
    public function onboardingUrl(Vendor $vendor): string;

    public function createTransfer(Payout $payout): string;
}
