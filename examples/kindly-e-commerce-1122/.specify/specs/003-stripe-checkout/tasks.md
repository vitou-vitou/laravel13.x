# Tasks: Phase 3a — Stripe Checkout (test)

**Status:** Complete (2026-06-01)  
**Arena:** `docs/ARENA_REVIEW_STRIPE_PHASE3A.md`

## Phase A — Setup

- [x] T301 `composer require stripe/stripe-php`
- [x] T302 Migration: `stripe_checkout_session_id`, `stripe_payment_intent_id` on `orders`
- [x] T303 `.env.example` Stripe vars + README Stripe CLI section

## Phase B — TDD

- [x] T304 `StripeWebhookTest` — signature, paid, idempotent, invalid
- [x] T305 `StripeCheckoutTest` — pending order + session metadata/amount
- [x] T306 `StripeCheckoutExpiredTest` — stock restore on expired
- [x] T307 Remove stub `OrderPaymentController` + tests updated

## Phase C — Implement

- [x] T308 `StripeCheckoutService` (create Session from pending order)
- [x] T309 Refactor checkout flow: pending order TX → redirect Stripe
- [x] T310 `StripeWebhookController` + CSRF except
- [x] T311 Cart/order UI: Pay with Stripe; success/cancel pages (no paid on success)
- [x] T312 Full `php artisan test` + update `BROWSER_VERIFICATION.md`
