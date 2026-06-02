# Kindly E-Commerce (`kindly-e-commerce-1122`)

**A Structured Study Packet**  
Built with an 8-principle learning method

---

## How to use this packet

This packet teaches how **Kindly E-Commerce** works: a Laravel 13 + Breeze demo shop with session cart, Stripe Checkout, webhooks, coupons, admin product CRUD, and order lifecycle (`pending` → `paid` → `shipped`). Content is drawn from the actual codebase in `examples/kindly-e-commerce-1122`, not generic e-commerce theory.

**Step 1 — Understanding** (Principles 1–4): build a correct mental model.  
**Step 2 — Automaticity** (Principles 5–8): quizzes, spacing, mixing, and overlearning so you can recall it under pressure.

### The 8 principles

| # | Principle | What you do |
|---|-----------|-------------|
| 1 | Map of the system | See how parts connect |
| 2 | Clear explanations | Learn core ideas in plain language |
| 3 | Different media | Same ideas as summary, diagram, analogy, table |
| 4 | Short lessons | Bite-sized micro-lessons |
| 5 | Test yourself | Quiz + flashcards + answer key |
| 6 | Wait to review | Spaced repetition schedule |
| 7 | Mix it up | Interleaved quiz |
| 8 | Don't stop | Overlearning plan |

### Table of contents

- [Step 1 — Understanding](#step-1--understanding)
  - [Principle 1 — Map of the system](#principle-1--map-of-the-system)
  - [Principle 2 — Clear explanations](#principle-2--clear-explanations)
  - [Principle 3 — Different media](#principle-3--different-media)
  - [Principle 4 — Short lessons](#principle-4--short-lessons)
- [Step 2 — Automaticity](#step-2--automaticity)
  - [Principle 5 — Test yourself](#principle-5--test-yourself)
  - [Principle 6 — Wait to review](#principle-6--wait-to-review)
  - [Principle 7 — Mix it up](#principle-7--mix-it-up)
  - [Principle 8 — Don't stop](#principle-8--dont-stop)
- [Appendix — Glossary](#appendix--glossary)

---

# Step 1 — Understanding

Your goal: a simple, accurate picture of what this app does and how money, stock, and order status move through it.

---

## Principle 1 — Map of the system

### Customer purchase flow (in order)

| Stage | What happens | Main code |
|-------|----------------|-----------|
| 1. Browse | Guest or user sees active products | `ShopController`, `shop/index` |
| 2. Cart | Items stored in **session** (not DB yet) | `CartService`, `CartController` |
| 3. Coupon | Optional discount applied to cart totals | `CouponService`, `CouponController` |
| 4. Checkout | Logged-in user creates **pending** order; stock decremented in DB transaction | `OrderPlacementService`, `CheckoutController` |
| 5. Pay | Redirect to Stripe Checkout (or local dev success page) | `StripeCheckoutService` / fakes |
| 6. Confirm paid | Stripe webhook `checkout.session.completed` → `paid` + email queued | `StripeWebhookHandler`, `OrderLifecycleService` |
| 7. Fulfillment | Admin marks **shipped**; customer gets shipped email | `OrderShipmentController` |

### Major subsystems (components)

| Subsystem | Responsibility | Key paths |
|-----------|----------------|-----------|
| **Storefront** | Catalog + cart UI | `ShopController`, `CartController`, views under `resources/views/shop`, `cart` |
| **Checkout** | Order creation from cart | `CheckoutController`, `OrderPlacementService` |
| **Payments (Stripe)** | Session + webhook integrity | `app/Services/Stripe/*`, `StripeWebhookController` |
| **Order lifecycle** | Status transitions + emails | `OrderLifecycleService`, `app/Mail/*` |
| **Orders (customer)** | List/detail + timeline | `OrderController`, `orders/*` views |
| **Admin** | Products CRUD + ship orders | `Admin/ProductController`, `Admin/OrderShipmentController`, `EnsureUserIsAdmin` |
| **Auth** | Breeze session login/register | `routes/auth.php`, middleware `auth`, `admin` |

### Map takeaway

> **Paid is never set by the success page.** Only the webhook (or local dev simulate route) moves `pending` → `paid`. That single rule explains most security tests in this project.

---

## Principle 2 — Clear explanations

### What is this application?

A **single-vendor** web shop: one store, many products, session-based cart, checkout creates an `orders` row, payment is delegated to **Stripe Checkout**, and fulfillment adds a `shipped` state. It is a learning/demo app in the `laravel13.x` monorepo, not multi-vendor marketplace software.

### How does the cart work?

The cart lives in the **session** (via `CartService`). Prices and stock are read from the database when you add or update items, so the server does not trust browser-submitted prices. Coupons attach a code to the session cart and adjust totals before checkout.

### How does checkout create an order?

`OrderPlacementService::placeFromCart()` runs inside a **database transaction**: it re-reads products, checks stock, creates an `Order` with `status = pending`, creates `order_items`, and decrements `stock_quantity`. If anything fails, the transaction rolls back.

### How does Stripe fit in?

After the pending order exists, `CheckoutController` calls `CreatesStripeCheckoutSession::createForOrder()`. The implementation is chosen in `AppServiceProvider`: **testing** → fake URL; **no Stripe secret** → local dev flow; **secret set** → real Stripe API. The customer pays on Stripe’s hosted page.

### When does an order become `paid`?

`StripeWebhookHandler` handles `checkout.session.completed`. It verifies signature, order id, session id match, and **amount_total** equals `order.total_cents`. Then `OrderLifecycleService::markPaid()` sets `paid`, `paid_at`, and queues `OrderPaidMail`. Duplicate webhooks are safe because already-`paid` orders short-circuit.

### What if checkout expires?

On `checkout.session.expired`, the handler restores stock via `Order::restoreStock()` inside a transaction, but only if the order is not already paid.

### How does `shipped` work?

Only an **admin** can POST to `admin/orders/{order}/ship`. `OrderLifecycleService::markShipped()` requires current status `paid`, then sets `shipped`, `shipped_at`, and queues `OrderShippedMail`. Pending orders cannot skip to shipped.

### Who can see an order?

`OrderController::show` allows the **order owner** or a user with `is_admin = true`. Other users get HTTP 403.

### How is the app tested?

PHPUnit feature tests cover cart, checkout, coupons, admin products, Stripe checkout redirect, webhook idempotency, order ownership, and admin shipment. Run: `php artisan test` (53 tests when Phase 3b lifecycle is included).

### Explanation takeaway

> **Core idea:** Server-authoritative pricing, webhook-authoritative payment, guarded state transitions.  
> **Common misconception:** “Returning from Stripe success URL means I paid.” In this app, that is **false**.

---

## Principle 3 — Different media

### One-line summary

Kindly E-Commerce is a Breeze-authenticated Laravel shop where session carts become pending orders, Stripe webhooks alone confirm payment, and admins ship paid orders while queued emails notify customers.

### Text diagram (money + status)

```
[Browse] → [Session cart] → [Checkout TX: pending order + stock↓]
                ↓
         [Stripe Checkout]
                ↓
    ┌───────────┴───────────┐
    │ webhook: completed    │ webhook: expired
    ↓                       ↓
 paid + email queued      stock restored
    ↓
 [Admin: mark shipped] → shipped + email queued
```

### Analogy

Think of a **restaurant tab**: you order (pending), the card terminal (Stripe) tells the kitchen you actually paid (webhook), not the customer walking back from the terminal (success page). Shipping is the kitchen calling “order on the way” (admin + shipped email).

### Comparison table

| Concept | This app | Often confused with |
|---------|----------|---------------------|
| Cart storage | PHP session | `carts` database table |
| Payment confirmation | Stripe webhook | Success redirect URL |
| Admin | `users.is_admin` boolean | Spatie roles / policies (not used here) |
| API auth | Session (Breeze) | Sanctum API tokens (not in this demo) |
| Stub pay | **Removed** | Old `POST /orders/{id}/pay` pattern |

### Media takeaway

> Meet the same flow as **table → diagram → analogy**. If you can draw the webhook branch from memory, you understand the hardest part.

---

## Principle 4 — Short lessons

**Lesson 1 — Three layers of “cart”**  
Session holds line items; database holds truth for price/stock; checkout copies session into permanent `orders` + `order_items`.

**Lesson 2 — Pending is a reservation**  
Creating a pending order reduces stock immediately so two checkouts cannot sell the last unit—unless expired webhook puts stock back.

**Lesson 3 — Stripe binding**  
`AppServiceProvider` picks real Stripe, local dev, or fake checkout. Tests never hit the network thanks to `FakeStripeCheckoutService`.

**Lesson 4 — Webhook contract**  
Signature + metadata `order_id` + amount match = trust. Anything else logs a warning and leaves order pending.

**Lesson 5 — Lifecycle service**  
All “business meaning” of paid/shipped (timestamps + mail) goes through `OrderLifecycleService`, not scattered in controllers.

**Lesson 6 — Admin is a thin gate**  
`EnsureUserIsAdmin` middleware on `/admin/*` routes; shipment is one POST, not a full warehouse system.

### Short-lessons takeaway

> Six lessons, six files worth knowing: `CartService`, `OrderPlacementService`, `AppServiceProvider`, `StripeWebhookHandler`, `OrderLifecycleService`, `routes/web.php`.

---

# Step 2 — Automaticity

Understanding fades without retrieval. Use the quiz, flashcards, schedule, and mixed quiz below.

---

## Principle 5 — Test yourself

### Quiz (10 questions)

1. Where is the shopping cart stored before checkout?  
2. What HTTP method and route start checkout?  
3. What order status is set when checkout succeeds?  
4. What event type marks an order paid?  
5. Does the checkout success page set `paid`?  
6. Name two checks `StripeWebhookHandler` performs before marking paid.  
7. What happens to stock when a Stripe session expires?  
8. Which service queues `OrderPaidMail`?  
9. Can a pending order be marked shipped? Who can ship?  
10. How many tests pass in the current suite (approx.)?

### Answer key

1. **Session** (`CartService`), not a `carts` table.  
2. **POST** `/checkout` (`checkout.store`), requires `auth`.  
3. **`pending`**.  
4. **`checkout.session.completed`**.  
5. **No** — tests assert success page does not mark paid.  
6. Any two of: valid signature, order resolved, session id match, amount_total = `total_cents`, order still pending.  
7. **Stock restored** via `restoreStock()` if not paid.  
8. **`OrderLifecycleService::markPaid()`**.  
9. **No** — only **paid** orders; **admin** via `admin.orders.ship`.  
10. **53** (as of Phase 3b implementation).

### Flashcards

| Front | Back |
|-------|------|
| Cart storage? | Session via `CartService` |
| Paid authority? | Stripe webhook (or dev simulate) |
| Success URL paid? | No |
| Expired session? | Restore stock if not paid |
| Shipped from status? | Only from `paid` |
| Admin flag column? | `users.is_admin` |
| Checkout interface? | `CreatesStripeCheckoutSession` |
| Webhook CSRF? | Exempt in `bootstrap/app.php` |
| Order owner view rule? | Owner OR admin |
| Local dev pay route? | `dev.stripe.simulate-paid` (local only) |
| Paid timestamp column? | `paid_at` |
| Shipped timestamp column? | `shipped_at` |

---

## Principle 6 — Wait to review

| When | What to do | Done |
|------|------------|------|
| **Today** | Read Principle 1 map; draw flow without notes | ☐ |
| **Day 1** | Quiz (Principle 5) closed-book; fix misses | ☐ |
| **Day 3** | Flashcards until 3 perfect rounds | ☐ |
| **Day 7** | Mixed quiz (Principle 7) | ☐ |
| **Day 14** | Open `routes/web.php` and trace one request | ☐ |
| **Day 30** | Cold draw: cart → paid → shipped on paper | ☐ |

Spacing works because each gap forces **retrieval**, which strengthens memory more than re-reading.

---

## Principle 7 — Mix it up

### Interleaved quiz (10 questions)

1. Which middleware protects `admin/orders/{order}/ship`?  
2. What binds `CreatesStripeCheckoutSession` in tests?  
3. Name the mailable for shipped orders.  
4. Can a guest apply a coupon?  
5. What column stores Stripe’s session id?  
6. Why is stub `POST /orders/{id}/pay` removed?  
7. What view shows order timeline?  
8. Idempotent webhook test name idea?  
9. Default admin email from seeder?  
10. Port used in docs for `artisan serve`?

### Interleaved answer key

1. **`admin`** (`EnsureUserIsAdmin`).  
2. **`FakeStripeCheckoutService`**.  
3. **`OrderShippedMail`**.  
4. **Yes** (cart coupon routes are not behind `auth`).  
5. **`stripe_checkout_session_id`**.  
6. **Forgeable paid** without gateway proof.  
7. **`orders/show.blade.php`**.  
8. e.g. **`test_duplicate_completed_webhooks_are_idempotent`**.  
9. **`admin@kindly.local`**.  
10. **8012**.

Mixing trains you to **pick** the right subsystem, not recite one chapter in order.

---

## Principle 8 — Don't stop

### Stages past first success

| Stage | Sign | Action |
|-------|------|--------|
| First correct | You pass quiz once | Same day: flashcards |
| Comfortable | You explain webhook flow aloud | Day 3–7: mixed quiz + trace routes |
| Automatic | You debug a failing test without docs | Monthly: re-run `artisan test` and predict failures |

### Overlearning plan

- Run `php artisan test --filter=Stripe` until you can name each test’s purpose.  
- Teach someone: “Why success URL ≠ paid.”  
- Add one new test idea (e.g. cannot ship twice) and sketch it in comments.  
- Re-read `decomposition-kindly-ecommerce.md` and label each box Done/Not started.

### Final takeaway

> **Step 1** gave you the map; **Step 2** makes it stick. Permanent knowledge = correct model + repeated retrieval + spaced mixed practice.

---

## Appendix — Glossary

| Term | Definition |
|------|------------|
| **Breeze** | Laravel auth scaffolding (login, register, session). |
| **Pending order** | Order created at checkout; payment not confirmed. |
| **Webhook** | HTTP POST from Stripe to your app with signed event payload. |
| **Idempotent** | Repeating the same webhook does not double-apply payment. |
| **OrderLifecycleService** | Central service for `paid` / `shipped` transitions and emails. |
| **Session cart** | Temporary cart array stored in the user’s PHP session. |
| **CSRF except** | `stripe/webhook` excluded from CSRF because Stripe is external. |
| **Local dev checkout** | Checkout without API key; simulate paid via dev route. |
| **Phase 3a** | Stripe Checkout + webhooks (complete). |
| **Phase 3b** | Lifecycle emails + shipped + timeline (implemented in code). |

### Further study

- `docs/study-packets/decomposition-kindly-ecommerce.md` — structural breakdown for reading.  
- `docs/NEXT_SESSION.md` — what to build next.  
- `.specify/specs/003-stripe-checkout/` and `003-order-lifecycle/` — formal specs.
