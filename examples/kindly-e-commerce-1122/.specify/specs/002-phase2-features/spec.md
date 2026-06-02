# Phase 2: Coupons, stub payment completion, admin catalog

**Status:** Complete (2026-06-01)

## Features

- **Coupons:** `KINDLY10` (10%), `SAVE500` ($5 off); apply on cart; snapshot on order
- **Stub pay:** Owner marks `pending` → `paid` via `POST /orders/{order}/pay`
- **Admin:** `is_admin` users manage products at `/admin/products`

## Tests

`CouponTest`, `StubPaymentTest`, `AdminProductTest` + existing suite.
