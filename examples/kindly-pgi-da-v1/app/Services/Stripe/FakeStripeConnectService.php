<?php

namespace App\Services\Stripe;

use App\Contracts\CreatesStripeConnectTransfer;
use App\Models\Payout;
use App\Models\Vendor;

class FakeStripeConnectService implements CreatesStripeConnectTransfer
{
    public function onboardingUrl(Vendor $vendor): string
    {
        if ($vendor->stripe_account_id === null) {
            $vendor->update([
                'stripe_account_id' => 'acct_fake_'.$vendor->id,
            ]);
        }

        return route('vendor.connect.callback');
    }

    public function createTransfer(Payout $payout): string
    {
        return 'tr_fake_'.$payout->id;
    }
}
