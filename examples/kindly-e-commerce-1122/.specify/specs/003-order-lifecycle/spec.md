# Feature Specification: Order lifecycle + notifications — PROPOSED

**Status:** Proposed (2026-06-01) — **not implemented**  
**Arena:** `docs/ARENA_DEEP_REVIEW_PHASE3.md` (P2)  
**Depends on:** Paid orders from Stripe or hardened stub

## User stories

1. **Confirmation email:** When order becomes `paid`, customer receives queued email with line items and totals.
2. **Shipped:** Admin marks order `shipped` (only if `paid`); customer optional shipped email.
3. **Customer view:** Order detail shows timeline: placed → paid → shipped.

## Functional requirements

- **FR-401:** `shipped` only from `paid`.
- **FR-402:** Emails queued (`ShouldQueue`); failures logged, order state unchanged.
- **FR-403:** Only order owner + admin can view order detail.

## Success criteria

- Tests: transition guards, mail sent/faked
- Browser: admin ships order; customer sees status

## OpenSpec

Preferred change name: `add-order-lifecycle`
