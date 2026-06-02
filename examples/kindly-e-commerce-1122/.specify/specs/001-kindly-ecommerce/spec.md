# Feature Specification: Kindly E-Commerce (Session Store MVP)

**Feature Branch**: `001-kindly-ecommerce`

**Created**: 2026-06-01

**Status**: MVP complete (2026-06-01)

**Input**: Full e-commerce backend MVP from Laravel 180+ catalog — catalog, cart, checkout, stub payment. External review via **Arena.ai only**.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Browse catalog (Priority: P1)

A visitor views a list of products with name and price.

**Independent Test**: `GET /` returns 200 with seeded products.

### User Story 2 - Manage cart (Priority: P1)

A visitor adds products to a session cart, updates quantities, and removes lines.

**Independent Test**: Cart totals use **database** `price_cents`, not posted prices.

### User Story 3 - Checkout (Priority: P1)

An authenticated user places an order from the cart; stock decreases; cart clears.

**Independent Test**: Guest checkout redirects to login; authenticated checkout creates `orders` + `order_items`.

### User Story 4 - Order history (Priority: P2)

An authenticated user sees only their own orders.

## Functional Requirements

- **FR-001**: System MUST list active products on the shop home.
- **FR-002**: Cart MUST be session-scoped; line prices from DB at display/checkout time.
- **FR-003**: Checkout MUST require authentication.
- **FR-004**: Checkout MUST reject quantities exceeding `stock_quantity`.
- **FR-005**: Orders MUST record `unit_price_cents` snapshot per line.
- **FR-006**: Stub payment: new orders have status `pending` (no live gateway in v1).

## Phase 2 (2026-06-01) — complete

- Coupons on cart (`KINDLY10`, `SAVE500`); order stores `subtotal_cents`, `discount_cents`, `coupon_code`
- Stub payment completion: owner pays `pending` → `paid`
- Admin product CRUD (`/admin/products`, `is_admin` users)

See `.specify/specs/002-phase2-features/spec.md` and `tasks-phase2.md`.

## Out of Scope (remaining)

- Live payment gateway (Stripe/PayPal), multi-vendor, API tokens

## Success Criteria

- **SC-001**: `php artisan test` covers catalog, cart, checkout, order ownership.
- **SC-002**: Arena.ai spec/plan review in `docs/ARENA_REVIEW_*.md`.
- **SC-003**: Browser verification documented in `docs/BROWSER_VERIFICATION.md`.
