# kindly-e-commerce-1122 — session resume

> **Parent handoff:** [`../../../docs/SESSION_STATE.md`](../../../docs/SESSION_STATE.md)

**Updated:** 2026-06-01 | **MVP + Phase 2 + Phase 3a (Stripe)** | **Tests:** 49/49

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

1. **Phase 3b** — order lifecycle (confirmation email, `shipped`) per `docs/ARENA_DEEP_REVIEW_PHASE3.md` / `.specify/specs/003-order-lifecycle/`
2. **OpenSpec** — `openspec init` only for post-MVP change orders (`docs/PRE_ACTION_PLAN.md`)
3. **Live browser Stripe** — blocked without real `STRIPE_SECRET` + `stripe listen`; PHPUnit fakes cover logic

**Do not:** re-scaffold Breeze, re-add stub pay, mark `paid` on success URL.
