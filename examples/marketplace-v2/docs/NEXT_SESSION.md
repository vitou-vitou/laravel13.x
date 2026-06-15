# marketplace-v2 — session resume

> **Roadmap:** [`../../../docs/marketplace-v1-97-task-roadmap.md`](../../../docs/marketplace-v1-97-task-roadmap.md)  
> **Parent:** [`../../../docs/SESSION_STATE.md`](../../../docs/SESSION_STATE.md)

**Updated:** 2026-06-14 | **Tests:** 75/75 | **Post-MVP roadmap complete (Phases 1–4)**

---

## Money & trust v1 (2026-06-14) — done

- **Refunds:** `/admin/orders/{order}` — partial/full refund, `refunds` table, payment audit trail
- **Stripe Connect:** vendor `/vendor/connect/start` → callback; fake transfers in test/local
- **Payouts:** `stripe_transfer_id`, dispute freeze, `released_at`
- **Promos v2:** vendor-scoped codes + `min_subtotal_cents` on admin promo form
- **Trust copy:** buyer protection on cart checkout + paid order detail
- `MoneyAndTrustTest` + `openspec/changes/money-and-trust-v1/design.md`

## Storefront polish v1 (2026-06-14) — done

- **Filters:** category + `sort` (newest / price) + `min_price` / `max_price` on `/` and `/catalog`
- **Home:** featured categories + session **recently viewed** after PDP visits
- **Mobile:** 2-col catalog grid, denser cards, `min-h-11` touch targets on PDP
- **Sticky cart:** mobile bottom bar (item count + total) — hidden on `/cart`
- `StorefrontPolishTest` + `CatalogQueryService` / `RecentlyViewedService`

## Seller experience v1 (2026-06-14) — done

- **Products:** http://marketplace-v2.test/vendor/products — create/edit draft or active + variants/stock
- **Order detail:** `/vendor/orders/{group}` — customer, ship-to snapshot, lines, timeline, confirm/ship/deliver
- **Low stock:** dashboard warns when variant stock ≤ 5
- **Nav:** Vendor link in main nav for vendor accounts
- `SellerExperienceTest` + updated `VendorDashboardTest`

## Buyer experience v1 (2026-06-14) — done

- **Addresses:** http://marketplace-v2.test/account/addresses
- **Wishlist:** heart on product page · http://marketplace-v2.test/wishlist
- **Order timeline** on order detail (Paid → Confirmed → Shipped → Delivered + tracking)
- Cart checkout: pick shipping address when saved
- Run `php artisan migrate` if tables missing locally

## Promo codes (2026-06-14)

- Platform-wide or **vendor-scoped** admin codes; optional min subtotal
- Customer applies on cart; discount on `order.total_cents` / `payment.amount_cents`
- **Admin:** http://marketplace-v2.test/admin/promo-codes
- `uses_count` increments when payment is marked paid

## UI (2026-06-14) — full pass

- **`docs/DESIGN.md`** — tokens + shared Blade components
- **Taobao-tier catalog** — chip categories, dense feed, `docs/screenshots/` (iPhone 14 / Pixel 7 / iPad via Playwright MCP)
- **MCP stack** — [`../../../docs/MCP_SERVERS.md`](../../../docs/MCP_SERVERS.md) (playwright, 21st magic, context7, browsermcp, **Notion plugin** — keys/OAuth in Cursor, not repo)
- **75/75 tests** — money/trust + storefront/seller/buyer/promo suite
- Refresh **http://marketplace-v2.test**

## Done

| Wave | Tasks | Status |
|------|-------|--------|
| 1–6 | T001–T050 | Domain, catalog, cart, multi-vendor checkout |
| 7 | T051–T057 | Stripe Checkout + webhooks + local dev simulate |
| 8 | T058–T063 | Vendor confirm/ship/deliver |
| 9 | T064–T068 | Payout schedule + release (Connect) |
| 10 | T069–T073 | Reviews + disputes + admin resolve |
| 11 | T074–T077 | Admin dashboard, commission, suspend, audit |
| 12 | T078–T097 | Scout search, GDPR, API health, security headers, CI, README |
| Post-MVP | Phases 1–4 | **Complete** — see [`openspec/ROADMAP.md`](../openspec/ROADMAP.md) |

---

## Run

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x/examples/marketplace-v2
php artisan migrate:fresh --seed
php artisan test
```

**Browser:** http://marketplace-v2.test

| Account | Email | Password |
|---------|-------|----------|
| Admin | admin@marketplace.local | password |
| Customer | customer@marketplace.local | password |
| Vendor | kindly-crafts@marketplace.local | password |

---

## Optional follow-ups (beyond roadmap)

- Live Stripe Connect + refund keys in production `.env`
- Filament admin panel (currently Blade)
- Horizon + queued notification jobs in production
- Archive OpenSpec changes under `openspec/changes/archive/`

**Do not:** re-scaffold this example.

---

## Verify

```bash
./bin/verify-example marketplace-v2
```
