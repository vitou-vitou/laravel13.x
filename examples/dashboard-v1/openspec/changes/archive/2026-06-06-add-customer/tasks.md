## 1. Schema

- [x] 1.1 `customers` migration (`name`, `email`)
- [x] 1.2 `orders.customer_id` FK; remove `customer_name`

## 2. Domain

- [x] 2.1 `Customer` model + factory; `Order` belongsTo
- [x] 2.2 Update seeder and metrics service

## 3. Filament

- [x] 3.1 `CustomerResource` CRUD
- [x] 3.2 Order form: customer relationship select

## 4. Tests & verify

- [x] 4.1 Unit + Filament feature tests
- [x] 4.2 `php artisan test` + `verify-example`
