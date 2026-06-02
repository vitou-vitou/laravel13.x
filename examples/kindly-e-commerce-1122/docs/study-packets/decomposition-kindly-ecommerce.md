# Kindly E-Commerce — System Decomposition

**Project:** `examples/kindly-e-commerce-1122`  
**Stack:** Laravel 13, Breeze (session auth), Blade, Stripe Checkout, PHPUnit  
**Purpose:** Reading map — how the software is split into parts, layers, and flows.  
**Companion:** [8-principle-study-kindly-ecommerce.md](./8-principle-study-kindly-ecommerce.md)

---

## Table of contents

1. [Context and boundaries](#1-context-and-boundaries)
2. [Subsystem decomposition](#2-subsystem-decomposition)
3. [Layer decomposition](#3-layer-decomposition)
4. [Data model decomposition](#4-data-model-decomposition)
5. [Request-flow decomposition](#5-request-flow-decomposition)
6. [Route map](#6-route-map)
7. [Service and dependency decomposition](#7-service-and-dependency-decomposition)
8. [Test decomposition](#8-test-decomposition)
9. [Status matrix (Done / Doing / Not started)](#9-status-matrix-done--doing--not-started)
10. [File index (quick navigation)](#10-file-index-quick-navigation)

---

## 1. Context and boundaries

### In scope (this app)

- Product catalog (read)
- Session cart (CRUD)
- Coupons on cart
- Authenticated checkout → `pending` order
- Stripe Checkout + webhooks
- Order list/detail for customers
- Admin: product CRUD, mark order shipped
- Queued lifecycle emails (paid, shipped)

### Out of scope (explicitly not built)

- Multi-vendor / seller accounts
- Sanctum mobile API
- Inventory warehouses or partial shipments
- Refunds/chargebacks automation
- Full RBAC beyond `is_admin`
- OpenSpec change workflow (not started)
- Production email provider configuration (uses Laravel mail + queue)

### External systems

| System | Role |
|--------|------|
| **Stripe** | Hosted checkout + signed webhooks |
| **MySQL/SQLite** | Orders, products, users (via Laravel DB) |
| **Queue worker** | Delivers `OrderPaidMail`, `OrderShippedMail` when `ShouldQueue` |
| **Browser** | Session cookie for auth + cart |

---

## 2. Subsystem decomposition

```
kindly-e-commerce-1122
├── Storefront (catalog, cart, coupon)
├── Checkout & placement
├── Payments (Stripe + dev fakes)
├── Order lifecycle (status + mail)
├── Customer orders (read)
├── Admin (products + ship)
└── Auth (Breeze + admin gate)
```

### 2.1 Storefront

| Unit | Files | Responsibility |
|------|-------|----------------|
| Catalog | `ShopController`, `shop/index.blade.php` | List active products |
| Cart | `CartController`, `CartService` | Session line items, qty, totals |
| Coupon | `CouponController`, `CouponService` | Apply/remove code on cart |

### 2.2 Checkout & placement

| Unit | Files | Responsibility |
|------|-------|----------------|
| Checkout entry | `CheckoutController::store` | Auth user → place order → redirect pay |
| Placement | `OrderPlacementService` | TX: validate stock, create order/items, decrement stock |
| Success/cancel | `CheckoutController::success/cancel` | Read-only pages (no paid on success) |

### 2.3 Payments

| Unit | Files | Responsibility |
|------|-------|----------------|
| Session factory | `StripeCheckoutService`, `Fake*`, `LocalDev*` | Create checkout URL |
| Binding | `AppServiceProvider`, `CreatesStripeCheckoutSession` | Environment-based implementation |
| Webhook ingress | `StripeWebhookController` | Verify signature, dispatch handler |
| Webhook logic | `StripeWebhookHandler` | completed / expired / payment_failed log |
| Dev simulate | `DevStripeSimulateController` | Local-only mark paid |

### 2.4 Order lifecycle

| Unit | Files | Responsibility |
|------|-------|----------------|
| Transitions | `OrderLifecycleService` | `markPaid`, `markShipped` |
| Mail | `OrderPaidMail`, `OrderShippedMail` | Queued notifications |
| Admin ship | `Admin/OrderShipmentController` | POST ship action |

### 2.5 Customer orders

| Unit | Files | Responsibility |
|------|-------|----------------|
| List | `OrderController::index` | Current user’s orders |
| Detail | `OrderController::show` | Owner or admin; timeline UI |

### 2.6 Admin

| Unit | Files | Responsibility |
|------|-------|----------------|
| Gate | `EnsureUserIsAdmin` | `is_admin` check |
| Products | `Admin/ProductController` | CRUD |
| Shipment | `Admin/OrderShipmentController` | paid → shipped |

### 2.7 Auth

| Unit | Files | Responsibility |
|------|-------|----------------|
| Breeze | `routes/auth.php`, Breeze views | Register, login, profile |
| Middleware | `auth`, `admin` aliases in `bootstrap/app.php` | Route protection |

---

## 3. Layer decomposition

Classic Laravel layering as used in this project:

```
┌─────────────────────────────────────────┐
│  Presentation (Blade, HTTP responses)    │
├─────────────────────────────────────────┤
│  HTTP (Controllers, Middleware)          │
├─────────────────────────────────────────┤
│  Application (Services, Mail, Contracts) │
├─────────────────────────────────────────┤
│  Domain (Models: Order, Product, User) │
├─────────────────────────────────────────┤
│  Infrastructure (DB, Stripe SDK, Queue) │
└─────────────────────────────────────────┘
```

| Layer | Examples | Rule in this app |
|-------|----------|------------------|
| Presentation | `resources/views/orders/show.blade.php` | No business rules; display + forms |
| HTTP | `CheckoutController` | Thin: authorize, delegate, redirect |
| Application | `OrderPlacementService`, `StripeWebhookHandler` | Business rules live here |
| Domain | `Order::isPaid()`, `restoreStock()` | Small helpers on models |
| Infrastructure | Migrations, `config/stripe.php`, Stripe API | Config + persistence |

---

## 4. Data model decomposition

### Entities

```
User 1──* Order 1──* OrderItem *──1 Product
Coupon (standalone rules; applied at cart/checkout via code string on order)
```

### `orders` row (conceptual)

| Field group | Columns | Notes |
|-------------|---------|-------|
| Identity | `id`, `user_id` | Owner |
| Money | `subtotal_cents`, `discount_cents`, `coupon_code`, `total_cents` | Server-calculated |
| Status | `status` | `pending`, `paid`, `shipped` |
| Stripe | `stripe_checkout_session_id`, `stripe_payment_intent_id` | Set during checkout/webhook |
| Lifecycle | `paid_at`, `shipped_at` | Phase 3b |
| Timestamps | `created_at`, `updated_at` | Placed time = `created_at` |

### State machine (order status)

```
pending ──(webhook completed / dev simulate)──► paid ──(admin ship)──► shipped

pending ──(webhook expired)──► pending + stock restored (status unchanged)
```

**Invalid transitions (guarded in code):**

- `pending` → `shipped` (rejected)
- `paid` → `paid` via duplicate webhook (no-op, idempotent)

---

## 5. Request-flow decomposition

### 5.1 Add to cart

```
Browser POST /cart/items
  → CartController::store
  → CartService::add
  → session['cart'] updated
  → redirect cart.index
```

### 5.2 Checkout

```
Browser POST /checkout (auth)
  → CheckoutController::store
  → OrderPlacementService::placeFromCart() [DB TX]
  → CreatesStripeCheckoutSession::createForOrder()
  → redirect Stripe or local success URL
```

### 5.3 Payment confirmation

```
Stripe POST /stripe/webhook (no CSRF)
  → StripeWebhookController
  → Webhook::constructEvent (signature)
  → StripeWebhookHandler::handle
  → OrderLifecycleService::markPaid (if completed)
  → Mail::queue(OrderPaidMail)
```

### 5.4 Admin ship

```
Browser POST /admin/orders/{id}/ship (auth + admin)
  → OrderShipmentController::store
  → OrderLifecycleService::markShipped
  → Mail::queue(OrderShippedMail)
  → redirect orders.show
```

---

## 6. Route map

| Method | Path | Name | Middleware |
|--------|------|------|------------|
| GET | `/` | `shop.index` | — |
| GET/POST/PATCH/DELETE | `/cart/*` | `cart.*` | — |
| POST | `/cart/coupon` | `cart.coupon.store` | — |
| POST | `/stripe/webhook` | `stripe.webhook` | — (CSRF except) |
| POST | `/checkout` | `checkout.store` | `auth` |
| GET | `/checkout/success/{order}` | `checkout.success` | `auth` |
| GET | `/orders`, `/orders/{order}` | `orders.*` | `auth` |
| POST | `/admin/orders/{order}/ship` | `admin.orders.ship` | `auth`, `admin` |
| resource | `/admin/products` | `admin.products.*` | `auth`, `admin` |

Auth routes: `routes/auth.php` (Breeze).

---

## 7. Service and dependency decomposition

### `AppServiceProvider` — Stripe checkout binding

```
testing        → FakeStripeCheckoutService
no STRIPE key  → LocalDevStripeCheckoutService
else           → StripeCheckoutService
```

### Service responsibilities

| Service | Input | Output / effect |
|---------|--------|-----------------|
| `CartService` | product id, qty | Session cart lines + totals |
| `CouponService` | coupon code | Discount on cart |
| `OrderPlacementService` | session cart | `Order` pending + stock↓ |
| `StripeCheckoutService` | `Order` | Stripe session URL |
| `StripeWebhookHandler` | Stripe `Event` | paid or stock restore |
| `OrderLifecycleService` | `Order` | status timestamps + mail queue |

### Contracts

- `CreatesStripeCheckoutSession` — allows swap for tests and local dev without changing `CheckoutController`.

---

## 8. Test decomposition

| Test class | Subsystem covered |
|------------|-------------------|
| `CartTest` | Storefront cart |
| `CheckoutTest` | Placement, pending |
| `CouponTest` | Coupons |
| `StripeCheckoutTest` | Redirect, success not paid |
| `StripeWebhookTest` | Signature, paid, idempotency, amount |
| `StripeCheckoutExpiredTest` | Stock restore |
| `AdminProductTest` | Admin CRUD |
| `OrderOwnershipTest` | 403 + admin view |
| `AdminOrderShipmentTest` | Ship guards + mail |
| `ExampleTest` | Smoke |

**Run:** `php artisan test` from project root (Herd PHP on Windows per repo docs).

---

## 9. Status matrix (Done / Doing / Not started)

### Subsystems

| Subsystem | Status | Notes |
|-----------|--------|-------|
| Storefront (catalog, cart, coupon) | **Done** | MVP + Phase 2 |
| Checkout & placement | **Done** | TX + pending |
| Stripe Checkout (3a) | **Done** | Webhooks, stub pay removed |
| Order lifecycle (3b) | **Done** | paid/shipped, emails, admin ship, 53 tests |
| Customer order views | **Done** | Timeline on show |
| Admin products | **Done** | CRUD |
| Auth (Breeze + admin) | **Done** | Session + `is_admin` |

### Cross-cutting capabilities

| Capability | Status | Notes |
|------------|--------|-------|
| Pessimistic stock locks (`lockForUpdate`) | **Not started** | Arena P1 recommendation |
| Coupon usage limits / per-user caps | **Not started** | Arena gap |
| Audit log (status/admin actions) | **Not started** | Arena P2 |
| Granular admin roles | **Not started** | Only boolean `is_admin` |
| Sanctum read API | **Not started** | Phase 3 option D |
| Multi-vendor | **Not started** | Explicitly deferred |
| OpenSpec change `add-order-lifecycle` | **Not started** | Code done; formal OpenSpec not |
| `003-order-lifecycle/tasks.md` | **Not started** | Spec exists; task checklist missing |
| Live browser Stripe E2E | **Blocked** | Needs real keys + `stripe listen` |
| `NEXT_SESSION.md` test count | **Doing** | Doc still says 49/49; code at 53 |

### Phases (product roadmap)

| Phase | Status |
|-------|--------|
| MVP (catalog, cart, checkout, orders) | **Done** |
| Phase 2 (coupons, admin products) | **Done** |
| Phase 3a (Stripe) | **Done** |
| Phase 3b (lifecycle email + shipped) | **Done** (implementation) |
| Phase 3c+ (stock locks, API, multi-vendor) | **Not started** |

---

## 10. File index (quick navigation)

| Concern | Path |
|---------|------|
| Routes | `routes/web.php`, `routes/auth.php` |
| Order model | `app/Models/Order.php` |
| Placement | `app/Services/OrderPlacementService.php` |
| Lifecycle | `app/Services/OrderLifecycleService.php` |
| Stripe | `app/Services/Stripe/*` |
| Webhook | `app/Http/Controllers/StripeWebhookController.php` |
| Config | `config/stripe.php` |
| Migrations | `database/migrations/2026_06_01_*`, `2026_06_02_210000_*` |
| Feature tests | `tests/Feature/*` |
| Session handoff | `docs/NEXT_SESSION.md` |
| Specs | `.specify/specs/001-kindly-ecommerce/`, `003-stripe-checkout/`, `003-order-lifecycle/` |

---

## Reading order (suggested)

1. This decomposition (structure).  
2. `routes/web.php` (wiring).  
3. `OrderPlacementService` + `StripeWebhookHandler` (core commerce logic).  
4. `OrderLifecycleService` (post-payment).  
5. `tests/Feature/StripeWebhookTest.php` (contract as tests).  
6. [8-principle study packet](./8-principle-study-kindly-ecommerce.md) (memorization).

---

*Last aligned to codebase: Phase 3b lifecycle (53 tests). Update status matrix when new phases land.*
