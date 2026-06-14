<?php

namespace App\Services;

use App\Enums\OrderGroupStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAuditLog;
use App\Services\PromoCodeService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function markPaid(Order $order, ?string $stripePaymentIntentId = null): Order
    {
        return DB::transaction(function () use ($order, $stripePaymentIntentId) {
            $order = $order->fresh(['payment', 'groups', 'promoCode']);

            $order->update([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
            ]);

            foreach ($order->groups as $group) {
                $group->update(['status' => OrderGroupStatus::Confirmed]);
            }

            if ($order->promo_code_id) {
                app(PromoCodeService::class)->recordUse($order->promoCode);
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

    public function recordRefund(Payment $payment, int $amountCents, string $note): Payment
    {
        return DB::transaction(function () use ($payment, $amountCents, $note) {
            $payment = $payment->fresh();
            $newRefunded = $payment->refunded_cents + $amountCents;

            $payment->update(['refunded_cents' => $newRefunded]);

            $toStatus = $newRefunded >= $payment->amount_cents
                ? PaymentStatus::Refunded
                : PaymentStatus::Completed;

            $this->transitionPayment($payment->fresh(), $toStatus, null, $note);

            return $payment->fresh();
        });
    }

    private function transitionPayment(
        Payment $payment,
        PaymentStatus $to,
        ?string $intentId = null,
        ?string $note = null,
    ): void {
        $from = $payment->status;

        $payment->update([
            'status' => $to,
            'stripe_payment_intent_id' => $intentId ?? $payment->stripe_payment_intent_id,
        ]);

        PaymentAuditLog::query()->create([
            'payment_id' => $payment->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'note' => $note,
        ]);
    }
}
