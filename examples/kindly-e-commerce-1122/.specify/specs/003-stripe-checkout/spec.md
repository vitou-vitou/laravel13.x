# Feature Specification: Stripe Checkout (test mode) — PROPOSED

**Status:** Arena-approved (2026-06-01) — **not implemented**  
**Arena:** `docs/ARENA_REVIEW_STRIPE_PHASE3A.md` (+ `docs/ARENA_DEEP_REVIEW_PHASE3.md`)  
**Parent:** `docs/PRE_ACTION_PLAN.md`

## Why

Replace forgeable stub `POST /orders/{order}/pay` with server-verified payment via Stripe Checkout (test keys).

## User stories (P1)

1. **Checkout session:** Authenticated user with non-empty cart → **pending order created in DB** (with stock decrement in same transaction) → redirect to Stripe Checkout; stays `pending` until webhook.
2. **Webhook:** `checkout.session.completed` (signed) marks order `paid` idempotently; verifies amount/currency; stores Stripe IDs.
3. **Expired:** `checkout.session.expired` restores stock; order remains unpaid.
4. **Success URL:** Shows confirmation only — **does not** set `paid` (Arena invariant).
5. **Cancel URL:** Order still `pending`.

## Functional requirements

- **FR-301:** Totals recomputed server-side before creating Checkout Session (same rules as today + coupons).
- **FR-302:** Webhook MUST verify Stripe signature; reject unsigned payloads.
- **FR-303:** Webhook handler MUST be idempotent (duplicate events safe).
- **FR-304:** Order MUST NOT transition to `paid` except via verified webhook; **remove** stub `POST /orders/{order}/pay`.
- **FR-305:** No card data touches Laravel app (Stripe-hosted Checkout only).
- **FR-306:** Stripe metadata MUST include `order_id`; webhook MUST match order owner and `total_cents`.
- **FR-307:** On `checkout.session.expired`, stock MUST be restored for that order’s line items.

## Out of scope

- Subscriptions, Connect/multi-vendor payouts, production keys in repo

## Success criteria

- Feature tests with Stripe mock/fake webhook fixture
- Manual test with Stripe CLI `stripe listen`
- `php artisan test` green

## OpenSpec

Preferred change name: `add-stripe-checkout-test`
