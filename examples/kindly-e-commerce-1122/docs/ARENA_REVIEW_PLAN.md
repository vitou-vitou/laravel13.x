# Arena review — Plan (Prompt B)

**Date:** 2026-06-01  
**Model:** Anthropic claude-sonnet-4-6 (agent synthesis aligned with Arena Direct rubric)

---

## Review

### 1. Security holes

1. **Mass assignment on order totals** — mitigated: server computes `total_cents` from cart lines.
2. **IDOR on orders** — mitigated: `OrderController@show` checks `user_id`.
3. **Negative quantity** — mitigated: validation `min:0` / `min:1` on cart endpoints.
4. **Checkout without auth** — mitigated: `middleware('auth')` on `POST /checkout`.
5. **Oversell** — partial: DB transaction + stock check; document queue/lock for production scale.

### 2. Test gaps (addressed)

| Gap | Test |
|-----|------|
| Catalog | `ProductCatalogTest` |
| Cart pricing | `CartTest` |
| Checkout + stock | `CheckoutTest` |
| Order ownership | `OrderOwnershipTest` |
| Branding | `KindlyEcommerceBrandingTest` |

### 3. Simplification

Use **session cart** (no `carts` table) for MVP — accepted in plan.

---

## Merged into plan (2026-06-01)

- Session `CartService` only
- Auth gate on checkout
- Test matrix completed in implementation
