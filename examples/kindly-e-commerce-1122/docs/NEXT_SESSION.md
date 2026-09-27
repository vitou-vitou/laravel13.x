# kindly-e-commerce-1122 — session resume

> **Parent handoff:** [`../../../docs/SESSION_STATE.md`](../../../docs/SESSION_STATE.md)

**Updated:** 2026-09-27 | **MVP + Phase 2 + 3a Stripe + 3b lifecycle + 3c stock locks** | **Tests:** 54/54

---

## Done (do not rebuild)

**MVP:** catalog, session cart, checkout, order history, Arena A+B

**Phase 2:** coupons, admin product CRUD

**Phase 3a (Stripe Checkout — test mode):**
- `stripe/stripe-php`; pending order + stock in TX → Stripe Checkout redirect
- `paid` only via `POST /stripe/webhook` (`checkout.session.completed`)
- `checkout.session.expired` restores stock
- Stub `POST /orders/{order}/pay` **removed**
- Spec: `.specify/specs/003-stripe-checkout/`
- Arena: `docs/ARENA_REVIEW_STRIPE_PHASE3A.md`

**Phase 3b (order lifecycle):**
- `OrderLifecycleService` — `paid` / `shipped` + queued `OrderPaidMail` / `OrderShippedMail`
- Admin `POST admin/orders/{order}/ship` (paid only)
- Spec: `.specify/specs/003-order-lifecycle/`

**Phase 3c (pessimistic stock locks):**
- `OrderPlacementService` — `lockForUpdate()` + sort by product id before decrement
- `Order::restoreStock()` — lock before increment (inside webhook TX)
- Spec: `.specify/specs/003-stock-locks/`
- SQLite tests prove sequential exhaust; true concurrent race needs MySQL

---

## Commands

```bash
cd d:/laravel13.x/examples/kindly-e-commerce-1122
/c/Users/vitou/.config/herd/bin/php.bat artisan migrate --seed
/c/Users/vitou/.config/herd/bin/php.bat artisan test
/c/Users/vitou/.config/herd/bin/php.bat artisan serve --host=127.0.0.1 --port=8012
```

**Admin:** `admin@kindly.local` / `password`

**Stripe (local):** set `STRIPE_*` in `.env`, then:

```bash
stripe listen --forward-to http://127.0.0.1:8012/stripe/webhook
```

Use Checkout test card `4242 4242 4242 4242`. Success URL does **not** mark paid — only webhook does.

---

## Default next work (autonomous loop OK)

1. **Coupon limits** — expiry / max uses / per-user (Arena gap) — Spec-Kit `003-coupon-limits` or OpenSpec
2. **Audit log** — status + admin actions (Arena P2)
3. **OpenSpec** — `openspec init` only for post-MVP change orders (`docs/PRE_ACTION_PLAN.md`)
4. **Live browser Stripe** — blocked without real `STRIPE_SECRET` + `stripe listen`; PHPUnit fakes cover logic

**Do not:** re-scaffold Breeze, re-add stub pay, mark `paid` on success URL.
