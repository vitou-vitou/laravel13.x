# Browser verification — kindly-e-commerce-1122

**URL:** http://127.0.0.1:8012  
**Server:** `php artisan serve --host=127.0.0.1 --port=8012`

## MVP (2026-06-01)

| Step | Result |
|------|--------|
| Shop catalog + cart + checkout | Pass |
| Register / login | Pass |
| Order #1 pending with lines | Pass |

## Phase 2 — related features (2026-06-01)

| Step | Result |
|------|--------|
| Cart shows **Coupon** apply form | Pass |
| Admin `/admin/products` | `admin@kindly.local` / `password` — `AdminProductTest` |

## Phase 3a — Stripe (2026-06-01)

| Step | Result |
|------|--------|
| Cart button **Pay with Stripe** | Pass (UI) |
| Checkout redirects to Stripe (needs `STRIPE_SECRET` in `.env`) | Manual — keys required |
| `paid` via webhook only (not success URL) | `StripeWebhookTest` (49/49) |
| Stub **Pay now** removed from order detail | Pass (UI) |
| `stripe listen --forward-to …/stripe/webhook` | Manual for live payment |

## PHPUnit

**49/49** — includes `StripeCheckoutTest`, `StripeWebhookTest`, `StripeCheckoutExpiredTest`.

## Seed data

- Products: `ProductSeeder`
- Coupons: `KINDLY10` (10% off), `SAVE500` ($5.00 off)
- Admin user: `admin@kindly.local` (password: `password`)
