## 1. Backend

- [x] 1.1 Install `laravel/reverb` + publish config
- [x] 1.2 `NewOrderCreated` event (`ShouldBroadcastNow`, private `orders`)
- [x] 1.3 Channel auth: `access_admin` permission
- [x] 1.4 Dispatch from `CheckoutService`

## 2. Frontend

- [x] 2.1 `resources/js/echo.js` + npm packages
- [x] 2.2 Echo listener in `order-notifications.js`
- [x] 2.3 Remove Livewire poll; refresh metrics on broadcast

## 3. Config & docs

- [x] 3.1 Reverb keys in `.env.example`
- [x] 3.2 `BROADCAST_CONNECTION=reverb`

## 4. Tests

- [x] 4.1 Event dispatch + payload unit tests
- [x] 4.2 Channel authorization feature test
- [x] 4.3 Dashboard no longer uses `wire:poll`
