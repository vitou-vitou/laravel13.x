## 1. Poll interval

- [x] 1.1 Change `wire:poll.30s` → `wire:poll.10s` in dashboard-metrics
- [x] 1.2 Update copy: "Auto-refreshes every 10s"

## 2. Shared notification JS

- [x] 2.1 Create `resources/js/order-notifications.js`
- [x] 2.2 Register Vite entry + load on dashboard
- [x] 2.3 Remove inline `@script` from dashboard-metrics

## 3. Checkout immediate alert

- [x] 3.1 Flash `checkout_customer` on checkout redirect
- [x] 3.2 Embed `#checkout-order-notification` JSON on dashboard
- [x] 3.3 Fire "Order placed" notification on DOMContentLoaded

## 4. Tests

- [x] 4.1 Assert poll.10s in dashboard tests
- [x] 4.2 Assert checkout session keys in CartPageTest
- [x] 4.3 DashboardPageTest for notification payload
