# Feature Specification: Multi-vendor Marketplace

**Feature Branch**: `001-multi-vendor-marketplace`

**Created**: 2026-06-01

**Status**: In progress

**Input**: Catalog entry *Multi-vendor marketplace* — decomposition in `LARAVEL_SPECIALIST_CAPABILITIES.md` (Live Decomposition Example).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Vendor lists products (Priority: P1)

A **vendor** registers with role `vendor`, creates a **store**, and publishes **products** with **variants** (SKU, price, stock).

**Independent Test**: Vendor creates product + variant; catalog API returns active listing.

**Acceptance Scenarios**:

1. **Given** a user with role vendor, **When** they complete store setup and create a variant with stock 10, **Then** the variant is purchasable.
2. **Given** variant stock 0, **When** customer adds to cart, **Then** request is rejected.

---

### User Story 2 - Customer cart & split checkout (Priority: P1)

A **customer** adds variants from one or more vendors to a **cart**, then **checks out**. System creates one **Order** and one **OrderGroup** per vendor with **OrderLines**; **commission** and **unit prices** are locked at checkout.

**Independent Test**: Cart with items from Vendor A and B → one Order, two OrderGroups; `order.total_cents` equals sum of group subtotals.

**Acceptance Scenarios**:

1. **Given** cart lines from two vendors, **When** checkout succeeds, **Then** exactly two order groups exist and commission_bps is stored on each group.
2. **Given** checkout, **When** complete, **Then** order line unit prices match variant prices at checkout time (immutable snapshot).
3. **Given** last unit in stock, **When** two customers checkout concurrently, **Then** exactly one succeeds.

---

### User Story 3 - Simulated payment (Priority: P1)

Customer **pays** a `pending_payment` order (no external gateway in v1). Payment amount equals order total; order becomes `paid` and groups `confirmed`.

**Independent Test**: POST pay → order status paid; payment status completed.

---

### User Story 4 - Vendor fulfillment scope (Priority: P2)

Vendor sees and updates only **their** order groups (confirm → processing → shipped).

**Independent Test**: Vendor B cannot load Vendor A's order group.

---

### Edge Cases

- Empty cart checkout → 422.
- Inactive product → cannot add to cart.
- Order total invariant: `order.total_cents` === sum(`order_groups.subtotal_cents`).
- Stock cannot go negative (DB transaction + lock).

### Out of scope (v1)

- Real Stripe/PayPal, payouts, disputes, reviews, Filament admin, platform categories.

## Requirements

- **FR-001**: API auth via Sanctum (`/api/register`, `/api/login`).
- **FR-002**: Roles: `customer`, `vendor`, `admin`.
- **FR-003**: Checkout splits cart lines by `vendor_id` into OrderGroups.
- **FR-004**: Commission rate locked on OrderGroup at checkout (`commission_bps`, `commission_cents`).
- **FR-005**: Vendor global scope on vendor-owned catalog and order groups.
- **FR-006**: Simulated payment marks order paid.

## Success Criteria

- **SC-001**: Pest suite covers split checkout, commission lock, stock concurrency, vendor isolation.
- **SC-002**: `php artisan test` green.
