## Data model

### `shipping_addresses`

| Column | Type | Notes |
|--------|------|--------|
| user_id | FK | owner |
| label | string | e.g. Home, Office |
| name | string | recipient |
| line1, line2 | string | |
| city, region, postal_code, country | string | |
| phone | string nullable | |
| is_default | boolean | one default per user |

### `orders` (add)

| Column | Type | Notes |
|--------|------|--------|
| shipping_address_snapshot | json | copy at checkout time |

### `wishlist_items`

| Column | Type | Notes |
|--------|------|--------|
| user_id, product_id | unique pair | |
| created_at | | |

## Services

- `ShippingAddressService` — set default, resolve for checkout
- `WishlistService` — toggle, list with products, move to cart
- Timeline: pure presentation — map `OrderGroupStatus` + `shipped_at` / `delivered_at` to steps (no new table)

## Routes (web, auth)

| Method | Path | Name |
|--------|------|------|
| GET/POST | `/account/addresses` | index, store |
| PATCH/DELETE | `/account/addresses/{address}` | update, destroy |
| POST | `/wishlist/{product}` | wishlist.store |
| DELETE | `/wishlist/{product}` | wishlist.destroy |
| GET | `/wishlist` | wishlist.index |

Checkout: optional `shipping_address_id` on `POST /checkout` (default address if omitted).

## UI

- Profile or `/account/addresses` — address list + forms (`store-input`, `x-store-page`)
- Product PDP + catalog card — heart toggle (auth only)
- `/wishlist` — grid like catalog, “Add to cart” per item
- `orders/show` — `x-order-timeline` component per group

## Tests (Feature)

- Address CRUD + default switching
- Checkout persists snapshot JSON
- Wishlist add/remove + add-to-cart
- Order page contains timeline labels for shipped group
