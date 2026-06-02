# Arena deep review — Phase 3 roadmap (Prompt C)

**Date:** 2026-06-01  
**Mode:** Arena Direct (not Battle, not Max)  
**Live chat:** https://arena.ai/c/019e83c1-90b9-73a6-813a-9189c0c80322  
**Prompt:** Phase 3 options A–E (Stripe, email/shipped, stock locks, Sanctum API, multi-vendor)

---

## Live Arena response (verbatim structure)

### 1) Top security/commerce gaps

1. **Payment integrity:** Stub `pending→paid` can be forged; no webhook signature, idempotency, or verified payment → fake paid orders.
2. **Stock race:** No atomic reservation at pay time → oversell under concurrency.
3. **Price/coupon trust:** Missing server-side reprice at payment, coupon usage limits, audit trail.
4. **Order state machine:** Weak transitions (`pending` → `paid` → `shipped`) without guards.
5. **Admin hardening:** No granular roles, rate limits, or audit logs on CRUD/coupon actions.

### 2) Rank A–E (1-week sprint)

| Rank | Option | Rationale |
|------|--------|-----------|
| 1 | **A) Stripe Checkout (test)** | Closes highest risk; fastest path to real payment validation |
| 2 | **C) Pessimistic stock locks** | Prevents oversell once real payments exist |
| 3 | **B) Order email + shipped** | Trust + ops; low risk after payment |
| 4 | **D) Sanctum read API** | Headless/mobile; limited commerce impact now |
| 5 | **E) Multi-vendor** | Split payments, permissions — not 1-week |

### 3) Recommended Phase 3 stories

**P1 (must-have)**

- Stripe Checkout (test): server-side session; reprice cart server-side; success/cancel URLs
- Webhooks: signature verify; idempotent `pending→paid`; store `payment_intent` / charge IDs
- Order state machine: policies; paid before shipped
- Stock: `SELECT … FOR UPDATE` at checkout; revalidate at payment confirmation
- Coupon enforcement: expiry, usage limits, per-user cap; lock on successful payment
- Basic audit log: status changes + admin coupon edits

**P2 (if time)**

- Queued order confirmation email on paid
- Shipped status + admin action + email
- Admin roles (super-admin vs catalog manager)
- Retry-safe webhook idempotency

### 4) Test gaps before coding

- Webhook signature / replay / out-of-order
- Concurrent checkout same SKU
- Repricing / tampered client ignored
- State transition guards
- Coupon limits + redemption race
- Webhook idempotency (no double stock decrement)

### 5) Do NOT build yet

- **Multi-vendor (E)** until single-vendor payment + stock are solid

---

## Agent reconciliation (codebase vs Arena)

| Arena claim | Current build (44 tests) | Action |
|-------------|--------------------------|--------|
| No server-side reprice | **Done** — `CartService` + `CheckoutController` use DB prices | Close; add **payment-time** reprice when Stripe lands |
| Stub pay forgeable | **True** — `POST /orders/{order}/pay` sets `paid` with no gateway | Phase 3a Stripe or signed payment token |
| Stock race | Partial — transaction + `fresh()` only | Phase 3b `lockForUpdate()` |
| Coupon limits | **Missing** — no expiry / max uses / per-user | Add in Phase 3 with Stripe |
| State machine | Only `pending` / `paid` | Add `shipped`, `cancelled` with policy |
| Admin audit | **Missing** | P2 audit log |

---

## Project decision (pre-action)

**Sprint recommendation (Spec-Kit + Superpowers + OpenSpec change order):**

1. **Change `add-stripe-checkout-test`** (OpenSpec) — P1 from Arena  
2. **Change `add-stock-locks`** — can parallel after checkout refactor  
3. **Change `add-order-lifecycle`** — email + shipped (Arena P2) after Stripe  

**Do not start coding** until `docs/PRE_ACTION_PLAN.md` gates pass and user picks track.

---

## Merge into artifacts

- Draft spec: `.specify/specs/003-stripe-checkout/spec.md` (status: **proposed**)
- Draft spec: `.specify/specs/003-order-lifecycle/spec.md` (status: **proposed**)
- Constitution amend: v1.1 — Phase 2 complete; Phase 3 requires webhooks + idempotency if Stripe
