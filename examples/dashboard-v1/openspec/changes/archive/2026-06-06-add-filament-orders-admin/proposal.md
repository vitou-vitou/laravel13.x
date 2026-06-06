## Why

Orders are only manageable via seeders/tinker. Staff need a CRUD admin to create, edit, and delete orders that feed the analytics dashboard.

## What Changes

- Install Filament v5 admin panel at `/admin` (shared `web` guard / Breeze users).
- Add `OrderResource` with form fields and table columns matching the `orders` schema.
- Feature tests for admin access control and order CRUD via Filament.

## Capabilities

### New Capabilities

- `filament-orders-admin`: Filament CRUD for orders.

### Modified Capabilities

- None.

## Impact

- `filament/filament`, panel provider, Filament theme assets
- New `app/Filament/Resources/Orders/*`
- Dashboard metrics automatically reflect admin-managed orders (same DB)
