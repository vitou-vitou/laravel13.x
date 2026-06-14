<?php

namespace App\Services;

use App\Enums\OrderGroupStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAuditLog;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function markPaid(Order $order, ?string $stripePaymentIntentId = null): Order
    {
        return DB::transaction(function () use ($order, $stripePaymentIntentId) {
            $order = $order->fresh(['payment', 'groups']);

            $order->update([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
            ]);

            foreach ($order->groups as $group) {
                $group->update(['status' => OrderGroupStatus::Confirmed]);
            }

            if ($order->payment) {
                $this->transitionPayment($order->payment, PaymentStatus::Completed, $stripePaymentIntentId);
            }

            return $order->fresh(['groups', 'payment']);
        });
    }

    public function markFailed(Order $order): Order
    {
        return DB::transaction(function () use ($order) {
            $order->update(['status' => OrderStatus::Cancelled]);
            $order->restoreStock();

            if ($order->payment) {
                $this->transitionPayment($order->payment, PaymentStatus::Failed);
            }

            return $order->fresh();
        });
    }

    private function transitionPayment(Payment $payment, PaymentStatus $to, ?string $intentId = null): void
    {
        $from = $payment->status;

        $payment->update([
            'status' => $to,
            'stripe_payment_intent_id' => $intentId ?? $payment->stripe_payment_intent_id,
        ]);

        PaymentAuditLog::query()->create([
            'payment_id' => $payment->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
        ]);
    }
}
