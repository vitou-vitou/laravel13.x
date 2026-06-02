# kindly-e-commerce-1122

Standalone **e-commerce** (catalog, session cart, Stripe Checkout test mode, webhooks) from the Laravel 180+ catalog. Built with **Spec-Kit + Superpowers**. External reviews use **Arena.ai only** — see [docs/ARENA_LOOP.md](docs/ARENA_LOOP.md).

**Resume:** [docs/NEXT_SESSION.md](docs/NEXT_SESSION.md)

## Run

```bash
cd d:/laravel13.x/examples/kindly-e-commerce-1122
/c/Users/vitou/.config/herd/bin/php.bat artisan migrate --seed
/c/Users/vitou/.config/herd/bin/php.bat artisan serve --host=127.0.0.1 --port=8012
```

Open http://127.0.0.1:8012 — browse products, cart, register/login, checkout.

## Tests

```bash
/c/Users/vitou/.config/herd/bin/php.bat artisan test
```

**49 tests** — catalog, cart, Stripe checkout/webhooks, coupons, admin CRUD, Breeze auth.

## Phase 2

- **Coupons:** `KINDLY10` (10% off), `SAVE500` ($5 off) — apply on cart page
- **Admin:** `admin@kindly.local` / `password` → **Admin** nav → `/admin/products`

## Stripe (Phase 3a)

Copy Stripe keys into `.env` (`STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_PRICE_CURRENCY=usd`).

```bash
stripe listen --forward-to http://127.0.0.1:8012/stripe/webhook
```

Cart → **Pay with Stripe**. Orders become `paid` only when Stripe sends `checkout.session.completed` to the webhook — not on the success return URL. Test card: `4242 4242 4242 4242`.
