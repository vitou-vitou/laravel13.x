## Why

Admins only get desktop alerts when on the dashboard. Email notifies staff when a new order is placed.

## What Changes

- Queued `NewOrderMail` to every user with the `admin` role on checkout
- Plain-text email: customer, total, line items, Filament edit link
- Tests with `Mail::fake()`

## Capabilities

### New

- `order-email-notifications`: admin mail on checkout
