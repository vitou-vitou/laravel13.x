## Why

Users can add products to a cart but cannot complete a purchase. Checkout closes the loop into orders, payments, and dashboard metrics.

## What Changes

- `CheckoutService` converts a non-empty cart into a customer order + completed payment.
- POST `/cart/checkout` clears the cart and redirects with confirmation.
- Cart UI shows a **Place order** button.

## Capabilities

### New

- `cart-checkout`: cart → order + payment transaction

### Modified

- `order-metrics`: new checkout orders feed KPIs automatically (same `orders` table)

## Impact

- `CheckoutService`, `CartController`, cart view, routes, tests
