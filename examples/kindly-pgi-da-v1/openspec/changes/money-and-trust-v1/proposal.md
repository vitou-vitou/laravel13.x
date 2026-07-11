## Why

Payments work via Stripe Checkout but trust features are thin: no refunds, Connect is stubbed, promos are platform-only.

## What Changes

- **Refunds** — admin-initiated full/partial refund; payment audit trail
- **Stripe Connect** — real vendor onboarding + payout release (replace stub where safe)
- **Promo depth** — vendor-scoped codes OR stack rules (document choice in design)
- **Buyer protection** copy on checkout + dispute link visibility

## Non-goals

- Multi-currency
- Escrow hold periods matching Taobao
- Wallet / installment payments

## Depends on

- Archive `storefront-polish-v1`
