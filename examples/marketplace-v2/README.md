# Multi-vendor Marketplace (marketplace-v2)

Laravel example: customers shop from multiple vendors; checkout splits into per-vendor order groups with locked commission; Stripe Checkout + webhooks; vendor fulfillment; payouts; disputes; reviews.

**URL:** http://marketplace-v2.test (Herd)

## Quick start

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/marketplace-v2
composer install
php artisan migrate:fresh --seed
php artisan test
```

From repo root: `./bin/verify-example marketplace-v2`

## Seeded accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@marketplace.local | password |
| Customer | customer@marketplace.local | password |
| Vendor | kindly-crafts@marketplace.local | password |

## Stripe (local)

Without `STRIPE_SECRET`, checkout uses local-dev mode: after placing an order you land on the success page with **Simulate Stripe payment**.

With real keys in `.env`:

- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`
- Webhook endpoint: `POST /stripe/webhook`
- Orders are marked paid only via webhook (not on success redirect)

## Features

- Multi-vendor cart checkout with `OrderGroup` split + price snapshots
- Stripe Checkout session + signed webhooks
- Vendor dashboard: confirm → ship → deliver → payout
- Customer reviews (verified purchase), disputes + admin resolution
- Admin: vendor approval/suspend, commission bps, orders dashboard, payment audit
- Scout product search (`?q=` on catalog)
- GDPR export (`GET /privacy/export`) and erase (`POST /privacy/erase`)
- API health: `GET /api/v1/health`

## Roadmap

Full 97-task plan: `docs/marketplace-v1-97-task-roadmap.md`
