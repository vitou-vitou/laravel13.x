# Arena review — Phase 3a Stripe Checkout (test mode)

**Date:** 2026-06-01  
**Mode:** Arena Direct  
**Live chat:** https://arena.ai/c/019e83c5-5117-78f7-8ee8-025ec122238c  
**Prior roadmap:** https://arena.ai/c/019e83c1-90b9-73a6-813a-9189c0c80322  

**Note:** This reply was labeled “OpenAI” in Arena UI (not Sonnet 4.6). Content merged below; re-run on claude-sonnet-4-6 if you want a second opinion.

---

## Prompt (Stripe-specific)

Laravel 13 Breeze Blade e-commerce; replace forgeable `POST /orders/{id}/pay` with Stripe Checkout **test mode**. SQLite; session cart; orders with `subtotal_cents`, `discount_cents`, `coupon_code`, `total_cents`; `pending|paid`.

Asked: order-before-session vs session-before-order; Cashier vs SDK; webhooks; invariants; PHPUnit cases; env vars; one mistake to avoid.

---

## Arena answers (merged)

### (1) Order vs payment sequence

**Create pending order + line items first**, then create Stripe Checkout Session; **reserve/decrement stock in the same DB transaction** as order creation.

**Why:** Stable `order_id` in Stripe metadata; auditable pending order; webhook maps to a real row; avoids creating orders only after payment (cart/session loss).

**Project adjustment:** Today stock decrements at checkout — keep that inside the transaction when creating the pending order before redirecting to Stripe.

### (2) Cashier vs `stripe/stripe-php`

**Use `stripe/stripe-php` only** — Checkout-only scope; no subscriptions/billing portal; less abstraction than Cashier.

### (3) Webhook events

| Event | Purpose |
|-------|---------|
| `checkout.session.completed` | Mark order `paid` (idempotent) |
| `checkout.session.expired` | Leave unpaid; **restore stock** / release reservation |
| `payment_intent.payment_failed` | Log / leave pending |

### (4) Security invariants

1. Never trust client cart prices/discounts — server-side totals only (already true; keep at Session create).
2. `order_id` in Stripe metadata; webhook must match DB order.
3. Verify webhook signature (`STRIPE_WEBHOOK_SECRET`) before any state change.
4. `pending` → `paid` **once**, idempotent on duplicate events.
5. Verify paid **amount + currency** match `order.total_cents` before `paid`.

### (5) PHPUnit tests (names + intent)

| Test | Assert |
|------|--------|
| `it_creates_pending_order_and_checkout_session_from_server_calculated_cart_totals` | `pending`, totals from DB, Session amount/metadata match |
| `it_marks_order_paid_on_checkout_session_completed_webhook` | Valid sig → `paid` |
| `it_ignores_duplicate_completed_webhooks_idempotently` | Second delivery no double effect |
| `it_rejects_webhook_with_invalid_signature` | Order stays `pending` |
| `it_restores_stock_when_checkout_session_expires` | Unpaid + stock restored |

### (6) Env vars

- `STRIPE_KEY` (publishable)
- `STRIPE_SECRET`
- `STRIPE_WEBHOOK_SECRET`
- `STRIPE_PRICE_CURRENCY` (e.g. `usd`)
- `APP_URL` (success/cancel URLs)

### (7) Mistake to avoid

**Do not mark `paid` on the success/return URL.** Only the webhook finalizes payment.

---

## Locked implementation decisions (post-Arena)

| Decision | Choice |
|----------|--------|
| SDK | `stripe/stripe-php` |
| Pay finalization | Webhook only |
| Stub `POST /orders/{order}/pay` | **Remove** (or 410 in tests) |
| Checkout UX | Replace “Place order” with “Pay with Stripe” → creates pending order + redirect |
| CSRF | Exclude `POST /stripe/webhook` |
| Tests | Mock Stripe client or fixture payloads + signature helper |

---

## Gate before code

- [x] Arena Prompt C (roadmap) — `ARENA_DEEP_REVIEW_PHASE3.md`
- [x] Arena Stripe deep dive — this file
- [ ] User says **implement 3a** (or equivalent)
- [ ] `stripe/stripe-php` + `.env.example` keys documented
- [ ] TDD: failing webhook tests first

---

## Next artifact

Implementation follows `.specify/specs/003-stripe-checkout/tasks.md` (to be generated on implement).
