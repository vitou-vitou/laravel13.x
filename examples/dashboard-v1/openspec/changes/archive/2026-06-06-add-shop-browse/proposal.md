## Why

Users can checkout a cart but have no storefront to discover products. A shop page closes the browse → cart loop.

## What Changes

- GET `/shop` — catalog of active products with optional category filter
- POST `/shop/products/{product}/cart` — add to cart (qty from form, default 1)
- Nav link **Shop**; success flash after add

## Capabilities

### New

- `shop-browse`: authenticated product catalog + add-to-cart

## Impact

- `ShopController`, `shop.blade.php`, routes, nav, tests
