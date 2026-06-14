<?php

namespace App\Services;

use App\Enums\DisputeStatus;
use App\Enums\OrderGroupStatus;
use App\Enums\PayoutStatus;
use App\Models\OrderGroup;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;

class PayoutService
{
    public function scheduleForDelivered(OrderGroup $group): Payout
    {
        return DB::transaction(function () use ($group) {
            $group = $group->fresh(['payout', 'dispute']);

            if ($group->payout) {
                return $group->payout;
            }

            $payout = Payout::query()->create([
                'vendor_id' => $group->vendor_id,
                'order_group_id' => $group->id,
                'status' => PayoutStatus::Pending,
                'amount_cents' => $group->vendorNetCents(),
            ]);

            if ((int) config('marketplace.payout_hold_days', 0) === 0) {
                $this->release($payout->fresh(['orderGroup.dispute']));
            } else {
                $payout->update(['status' => PayoutStatus::Scheduled]);
            }

            return $payout->fresh();
        });
    }

    public function release(Payout $payout): Payout
    {
        return DB::transaction(function () use ($payout) {
            $payout = $payout->fresh(['orderGroup.dispute']);

            if ($payout->status === PayoutStatus::Completed) {
                return $payout;
            }

            if ($this->isFrozenByDispute($payout)) {
                return $payout;
            }

            $payout->update(['status' => PayoutStatus::Completed]);

            return $payout;
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
