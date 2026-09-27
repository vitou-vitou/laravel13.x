# Feature Specification: Pessimistic stock locks — Phase 3c

**Status:** Implemented (2026-09-27)  
**Arena:** `docs/ARENA_DEEP_REVIEW_PHASE3.md` (option C, rank 2)  
**Depends on:** Phase 3a Stripe + Phase 3b lifecycle

## User stories

1. **No oversell:** Two checkouts for the last unit cannot both succeed.
2. **Deterministic TX:** Stock rows locked inside the placement transaction before decrement.
3. **Safe restore:** Expired/cancelled pending orders restore stock without racing another checkout.

## Functional requirements

- **FR-501:** Inside `OrderPlacementService` TX, load each product with `lockForUpdate()` (sorted by product id to avoid deadlock), then re-check qty and decrement.
- **FR-502:** Pre-TX cart check may stay optimistic; authoritative check is post-lock.
- **FR-503:** `Order::restoreStock()` locks each product row before increment.
- **FR-504:** No schema change.

## Success criteria

- Existing checkout / Stripe / expired-restore tests still pass.
- New test: stock=1 → first checkout ok, second fails with cart error; stock stays 0 after first.

## LDA-PO (Quick)

- **L:** lock → recheck → decrement/increment; sort ids
- **D:** unchanged columns
- **A:** `OrderPlacementService` + `Order::restoreStock`
- **P:** same checkout UX / errors
- **O:** SQLite tests prove sequential exhaust; real race needs MySQL/`FOR UPDATE`
