## Why

Dashboard relied on 10s Livewire polling for new-order alerts. Echo + Reverb gives instant updates.

## What Changes

- `NewOrderCreated` broadcast event on checkout
- Private `orders` channel (admin only)
- Laravel Echo client replaces poll for notifications + KPI refresh
- Reverb config in `.env.example`

## Capabilities

### New

- `order-echo-broadcast`: real-time order alerts via Reverb

### Modified

- `order-desktop-notifications`: Echo listener instead of poll dispatch
