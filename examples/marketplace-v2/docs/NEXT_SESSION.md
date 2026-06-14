# marketplace-v2 — session resume

> **Roadmap:** [`../../../docs/marketplace-v1-97-task-roadmap.md`](../../../docs/marketplace-v1-97-task-roadmap.md)  
> **Parent:** [`../../../docs/SESSION_STATE.md`](../../../docs/SESSION_STATE.md)

**Updated:** 2026-06-14 | **Tests:** 39/39 | **MVP + storefront UI pass**

---

## UI (2026-06-14) — full pass

- **`docs/DESIGN.md`** — tokens + shared Blade components (`store-page`, `flash-status`, `admin-subnav`)
- **All customer/vendor/admin Blade pages** aligned to DESIGN.md (not only catalog)
- Auth uses branded guest layout; Breeze components use `btn-brand` / `store-input`
- **39/39 tests** — behavior unchanged
- Refresh **http://marketplace-v2.test**

## Done

| Wave | Tasks | Status |
|------|-------|--------|
| 1–6 | T001–T050 | Domain, catalog, cart, multi-vendor checkout |
| 7 | T051–T057 | Stripe Checkout + webhooks + local dev simulate |
| 8 | T058–T063 | Vendor confirm/ship/deliver |
| 9 | T064–T068 | Payout schedule + release (Connect stub) |
| 10 | T069–T073 | Reviews + disputes + admin resolve |
| 11 | T074–T077 | Admin dashboard, commission, suspend, audit |
| 12 | T078–T097 | Scout search, GDPR, API health, security headers, CI, README |

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

## Optional follow-ups

- Live Stripe Connect onboarding (real `stripe_account_id` transfers)
- Filament admin panel (currently Blade)
- Horizon + queued notification jobs in production
- OpenSpec change proposals for post-MVP iterations

**Do not:** re-scaffold this example.

---

## Verify

```bash
./bin/verify-example marketplace-v2
```
