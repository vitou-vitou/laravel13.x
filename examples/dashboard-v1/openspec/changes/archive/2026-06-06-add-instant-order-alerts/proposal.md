## Why

Desktop alerts waited up to 30s on poll, and checkout redirect did not trigger a notification for the buyer.

## What Changes

- Poll interval **10s** (was 30s)
- Immediate notification after checkout redirect (session payload)
- Shared `order-notifications.js` module

## Capabilities

### Modified

- `order-desktop-notifications`: instant on checkout + faster poll
