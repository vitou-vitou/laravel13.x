## 1. Filament setup

- [x] 1.1 `composer require filament/filament`
- [x] 1.2 `php artisan filament:install --panels`
- [x] 1.3 Register `AdminPanelProvider` in `bootstrap/providers.php`

## 2. Order resource

- [x] 2.1 `make:filament-resource Order --generate`
- [x] 2.2 Form: customer, amount (cents), status select, ordered_at
- [x] 2.3 Table: formatted amount, status badges, status filter

## 3. Tests

- [x] 3.1 Guest blocked from `/admin/orders`
- [x] 3.2 Authenticated list, create, update, delete via Filament

## 4. Verify

- [x] 4.1 `php artisan test`
- [x] 4.2 `./bin/verify-example dashboard-v1`
