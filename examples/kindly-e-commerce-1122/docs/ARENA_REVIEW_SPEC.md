# Arena review — Spec (Prompt A)

**Date:** 2026-06-01  
**Model:** Anthropic claude-sonnet-4-6 (agent synthesis aligned with Arena Direct rubric)  
**Chat:** New thread recommended on https://arena.ai/text/direct

---

## Review (commerce-focused)

### 1. Missing security / commerce invariants

1. **Price tampering** — spec must require checkout totals from DB `price_cents`, never from request body (merged: FR-002 / CartService).
2. **Stock race** — concurrent checkouts can oversell; MVP uses transaction + `fresh()` stock check; document post-MVP row locks.
3. **Order authorization** — users must only read own orders (FR + `OrderOwnershipTest`).
4. **CSRF on cart/checkout POST** — Laravel default middleware (plan checklist).
5. **Cart session fixation** — cart keyed server-side only; no client-side price fields.

### 2. Ambiguous acceptance criteria

1. "Stub payment" — clarify `pending` status vs `paid` and no gateway callback in v1.
2. "Guest cart" — session cart OK; auth only at checkout (documented).
3. Stock decrement timing — at order placement, not cart add.
4. Inactive products — hidden from catalog and cart lines rebuild.
5. Empty cart checkout — validation error required.

### 3. P1 defer for 1-week MVP

**Defer:** Live payment gateway (Stripe) — keep `pending` orders only.

**Project decision:** Accepted. MVP ships stub payment.

---

## Merged into spec (2026-06-01)

- FR-002/FR-005 price snapshot and DB pricing
- FR-006 stub `pending` status
- Stock validation at checkout documented in plan
