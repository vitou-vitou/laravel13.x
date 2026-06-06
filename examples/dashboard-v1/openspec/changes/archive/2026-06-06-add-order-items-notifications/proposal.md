## Why

Checkout only stores order totals — no product snapshot. Operators also want desktop alerts when new orders arrive on the dashboard.

## What Changes

- `order_items` table + snapshot lines on checkout
- Dashboard Livewire poll dispatches `new-order` for browser Notification API
- Enable-notifications control on dashboard

## Capabilities

### New

- `order-line-items`: cart → order item snapshots
- `order-desktop-notifications`: browser alerts on new orders

## Impact

- Migration, `OrderItem`, `CheckoutService`, dashboard Livewire, tests
