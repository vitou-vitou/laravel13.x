## Why

Orders store a free-text `customer_name`. A first-class `Customer` model enables reuse across orders, admin CRUD, and consistent dashboard display.

## What Changes

- Add `customers` table (`name`, optional unique `email`).
- Replace `orders.customer_name` with `orders.customer_id` foreign key.
- Filament `CustomerResource` for CRUD; order forms pick a customer via relationship.
- Seeders, factories, and metrics use `customer.name`.

## Capabilities

### New Capabilities

- `customer`: Customer entity, order relationship, Filament admin.

### Modified Capabilities

- `order-metrics`: Recent orders show linked customer name.
- `filament-orders-admin`: Order form uses customer select.

## Impact

- Migrations, `Order` / `Customer` models, Filament resources, tests, seeders
