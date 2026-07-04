<?php

namespace App\Services;

use App\Contracts\CreatesStripeRefund;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RefundService
{
    public function __construct(
        private PaymentService $payments,
        private CreatesStripeRefund $stripeRefunds,
    ) {}

    public function refundableCents(Payment $payment): int
    {
        return max(0, $payment->amount_cents - $payment->refunded_cents);
    }

    public function issue(Order $order, int $amountCents, string $reason, User $admin): Refund
    {
        $order = $order->fresh(['payment']);

        if (! $order->isPaid()) {
            throw ValidationException::withMessages([
                'amount_cents' => 'Only paid orders can be refunded.',
            ]);
        }

        $payment = $order->payment;

        if ($payment === null || $payment->status === PaymentStatus::Refunded) {
            throw ValidationException::withMessages([
                'amount_cents' => 'This payment cannot be refunded.',
            ]);
        }

        if ($amountCents <= 0 || $amountCents > $this->refundableCents($payment)) {
            throw ValidationException::withMessages([
                'amount_cents' => 'Refund amount must be between 1 and '.$this->refundableCents($payment).' cents.',
            ]);
        }

        return DB::transaction(function () use ($order, $payment, $amountCents, $reason, $admin) {
            $refund = Refund::query()->create([
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'initiated_by_user_id' => $admin->id,
                'amount_cents' => $amountCents,
                'status' => RefundStatus::Processing,
                'reason' => $reason,
            ]);

            $stripeRefundId = $this->stripeRefunds->create($payment, $refund);

            $refund->update([
                'stripe_refund_id' => $stripeRefundId,
                'status' => RefundStatus::Completed,
            ]);

            $this->payments->recordRefund($payment, $amountCents, "Admin refund #{$refund->id}: {$reason}");

            if ($this->refundableCents($payment->fresh()) === 0) {
                $order->update(['status' => OrderStatus::Refunded]);
            }

            return $refund->fresh();
        });
    }

    public function completeFromWebhook(Payment $payment, int $amountCents, string $stripeRefundId, string $note): void
    {
        if (Refund::query()->where('stripe_refund_id', $stripeRefundId)->exists()) {
            return;
        }

        DB::transaction(function () use ($payment, $amountCents, $stripeRefundId, $note) {
            Refund::query()->create([
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'initiated_by_user_id' => $payment->order->user_id,
                'amount_cents' => $amountCents,
                'status' => RefundStatus::Completed,
                'reason' => 'Stripe webhook',
                'stripe_refund_id' => $stripeRefundId,
            ]);

            $this->payments->recordRefund($payment, $amountCents, $note);

            if ($this->refundableCents($payment->fresh()) === 0) {
                $payment->order->update(['status' => OrderStatus::Refunded]);
            }
        });
    }
}
