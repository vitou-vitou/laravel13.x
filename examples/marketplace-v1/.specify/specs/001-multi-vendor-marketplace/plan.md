# Plan: Multi-vendor Marketplace API

**Stack**: Laravel 13, Sanctum, SQLite, JSON API.

## Architecture

```
Customer → CartService → CheckoutService → Order + OrderGroups + OrderLines
                                        → stock decrement (lockForUpdate)
Vendor   → Product CRUD (scoped) → OrderGroup fulfillment
Payment  → PaymentService (simulated gateway)
```

## Data model (v1)

| Model | Notes |
|-------|--------|
| `Vendor` | `user_id`, `store_name`, `commission_bps` (default 1000) |
| `Product` | `vendor_id`, `name`, `status` |
| `ProductVariant` | `sku`, `price_cents`, `stock_qty` |
| `Cart` / `CartLine` | One active cart per customer |
| `Order` | `customer_id`, `total_cents`, `status` |
| `OrderGroup` | Per vendor: `subtotal_cents`, `commission_bps`, `commission_cents` |
| `OrderLine` | Price snapshot per line |
| `Payment` | `order_id`, `amount_cents`, `status` |

## API routes

- `POST /api/register`, `/api/login`, `/api/logout`
- `POST /api/vendor/setup` — create vendor + first product
- `POST /api/vendor/products`, `POST /api/vendor/products/{product}/variants`
- `POST /api/cart/lines`, `GET /api/cart`
- `POST /api/checkout`
- `POST /api/orders/{order}/pay`
- `GET /api/my/orders`
- `GET /api/vendor/order-groups`, `POST .../ship`

## Production checklist (later)

- Real payment webhooks, payout holds, dispute freeze, Horizon, PostgreSQL.
