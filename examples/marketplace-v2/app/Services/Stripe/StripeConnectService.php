<?php

namespace App\Services\Stripe;

use App\Contracts\CreatesStripeConnectTransfer;
use App\Models\Payout;
use App\Models\Vendor;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;
use Stripe\Transfer;

class StripeConnectService implements CreatesStripeConnectTransfer
{
    public function onboardingUrl(Vendor $vendor): string
    {
        Stripe::setApiKey(config('stripe.secret'));

        $accountId = $vendor->stripe_account_id;

        if ($accountId === null) {
            $account = Account::create([
                'type' => 'express',
                'metadata' => ['vendor_id' => (string) $vendor->id],
            ]);
            $accountId = $account->id;
            $vendor->update(['stripe_account_id' => $accountId]);
        }

        $link = AccountLink::create([
            'account' => $accountId,
            'refresh_url' => route('vendor.connect.start'),
            'return_url' => route('vendor.connect.callback'),
            'type' => 'account_onboarding',
        ]);

        return $link->url;
    }

    public function createTransfer(Payout $payout): string
    {
        Stripe::setApiKey(config('stripe.secret'));

        $accountId = $payout->vendor->stripe_account_id;

        if ($accountId === null || $accountId === '') {
            throw new \RuntimeException('Vendor has no Connect account.');
        }

        $transfer = Transfer::create([
            'amount' => $payout->amount_cents,
            'currency' => config('stripe.currency', 'usd'),
            'destination' => $accountId,
            'metadata' => [
                'payout_id' => (string) $payout->id,
                'order_group_id' => (string) $payout->order_group_id,
            ],
        ]);

        return $transfer->id;
    }
}
