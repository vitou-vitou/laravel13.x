<?php

namespace App\Services;

use App\Contracts\CreatesStripeConnectTransfer;
use App\Enums\DisputeStatus;
use App\Enums\OrderGroupStatus;
use App\Enums\PayoutStatus;
use App\Models\OrderGroup;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;

class PayoutService
{
    public function __construct(private CreatesStripeConnectTransfer $connect) {}

    public function scheduleForDelivered(OrderGroup $group): Payout
    {
        return DB::transaction(function () use ($group) {
            $group = $group->fresh(['payout', 'dispute']);

            if ($group->payout) {
                return $group->payout;
            }

            $holdDays = (int) config('marketplace.payout_hold_days', 0);
            $scheduledFor = $holdDays > 0 ? now()->addDays($holdDays) : null;

            $payout = Payout::query()->create([
                'vendor_id' => $group->vendor_id,
                'order_group_id' => $group->id,
                'status' => PayoutStatus::Pending,
                'amount_cents' => $group->vendorNetCents(),
                'scheduled_for' => $scheduledFor,
            ]);

            if ($holdDays === 0) {
                $this->release($payout->fresh(['orderGroup.dispute', 'vendor']));
            } else {
                $payout->update(['status' => PayoutStatus::Scheduled]);
            }

            return $payout->fresh();
        });
    }

    public function release(Payout $payout): Payout
    {
        return DB::transaction(function () use ($payout) {
            $payout = $payout->fresh(['orderGroup.dispute', 'vendor']);

            if ($payout->status === PayoutStatus::Completed) {
                return $payout;
            }

            if ($this->isFrozenByDispute($payout)) {
                return $payout;
            }

            if ($payout->scheduled_for !== null && $payout->scheduled_for->isFuture()) {
                return $payout;
            }

            $group = $payout->orderGroup;

            if ($group !== null && ! in_array($group->status, [OrderGroupStatus::Delivered, OrderGroupStatus::Completed], true)) {
                return $payout;
            }

            $payout->update(['status' => PayoutStatus::Processing]);

            $transferId = null;

            if ($payout->vendor?->stripe_account_id) {
                $transferId = $this->connect->createTransfer($payout);
            }

            $payout->update([
                'status' => PayoutStatus::Completed,
                'stripe_transfer_id' => $transferId,
                'released_at' => now(),
            ]);

            return $payout->fresh();
        });
    }

    public function isFrozenByDispute(Payout $payout): bool
    {
        $dispute = $payout->orderGroup?->dispute;

        if ($dispute === null) {
            return false;
        }

        return ! in_array($dispute->status, [
            DisputeStatus::ResolvedBuyer,
            DisputeStatus::ResolvedVendor,
        ], true);
    }
}
