# money-and-trust-v1 — design notes

## Promo depth (v2)

**Choice: vendor-scoped codes, no stacking.**

- Each cart holds **at most one** promo code (unchanged from v1).
- `vendor_id` null → platform-wide; discount applies to full cart subtotal.
- `vendor_id` set → discount applies only to lines from that vendor (`applicableSubtotalCents`).
- Optional `min_subtotal_cents` is evaluated against the applicable subtotal (vendor slice or full cart).
- **No stacking** of multiple codes — keeps checkout math and payout reconciliation simple.

Platform still absorbs the discount on `order.total_cents` / `payment.amount_cents`; vendor group subtotals are unchanged.

## Refunds

- Admin-initiated via `RefundService`; Stripe refund in production, fake in testing.
- `charge.refunded` webhook dedupes by `stripe_refund_id`.
- Payment audit log records each refund transition.

## Connect payouts

- Fake Connect service in testing/local: onboarding sets `stripe_account_id`, transfers return `tr_fake_*`.
- `PayoutService::release` skips transfer when no Connect account; completes payout anyway (MVP behavior).
- Open disputes (not resolved) freeze payout release.
