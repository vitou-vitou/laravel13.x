# marketplace-v2 — post-MVP roadmap (Taobao-tier direction)

**Current:** MVP + promo codes + **all four post-MVP phases** · **75/75 tests** · not a full consumer super-app.

| Phase | Change slug | Focus | Outcome |
|-------|-------------|--------|---------|
| 1 | `buyer-experience-v1` | Buyer | **Done** — addresses, wishlist, order timeline |
| 2 | `seller-experience-v1` | Seller | **Done** — vendor product CRUD + richer order UI |
| 3 | `storefront-polish-v1` | Look & feel | **Done** — filters, mobile density, sticky cart, home sections |
| 4 | `money-and-trust-v1` | Money & trust | **Done** — refunds, Connect payouts, promo depth, buyer protection |

## Roadmap status

All planned post-MVP phases are implemented. Next work is optional (production Stripe, Filament, archive OpenSpec changes) — see [`docs/NEXT_SESSION.md`](../docs/NEXT_SESSION.md).

## How to verify

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/marketplace-v2
php artisan migrate
php artisan test
./bin/verify-example marketplace-v2
```

## What this is NOT

- Not a 97-task ZERO-MISS re-run
- Not Taobao parity — each phase added **credible depth** one slice at a time
