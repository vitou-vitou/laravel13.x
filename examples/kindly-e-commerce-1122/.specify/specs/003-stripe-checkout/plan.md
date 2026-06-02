# Plan: Stripe Checkout (test) — APPROVED (Arena 3a)

**Spec:** [spec.md](./spec.md)  
**Arena:** [docs/ARENA_REVIEW_STRIPE_PHASE3A.md](../../../docs/ARENA_REVIEW_STRIPE_PHASE3A.md)

## Summary

`stripe/stripe-php` only. Flow: **pending order + lines + stock in one transaction** → Stripe Checkout Session → redirect. **Webhook only** marks `paid`. Handle `checkout.session.expired` to restore stock.

## Stack

- `stripe/stripe-php`
- Env: `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_PRICE_CURRENCY`, `APP_URL`
- Webhook route excluded from CSRF

## Routes

| Method | Path | Notes |
|--------|------|-------|
| POST | `/checkout/stripe` | auth; create pending order + Session; redirect |
| POST | `/stripe/webhook` | signature verify |
| GET | `/checkout/success` | auth; flash only — **no paid** |
| GET | `/checkout/cancel` | auth; order stays pending |
| ~~POST~~ | ~~`/orders/{order}/pay`~~ | **removed** |

## Test matrix

See `tasks.md` T304–T307 (Arena-named tests).

## Superpowers

1. Red → green webhook tests with fixture JSON  
2. `verification-before-completion` before claiming done  
3. Manual: `stripe listen --forward-to localhost:8012/stripe/webhook`
