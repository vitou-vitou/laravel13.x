## Why

The dashboard example needs a realistic commerce domain (catalog, cart, payments) and staff RBAC (users, roles, permissions) with multilingual catalog labels.

## What Changes

- **Catalog:** `Category`, `Product` (Spatie translatable `name` / `description`).
- **Cart:** per-user cart with line items; `CartService` for add/update/total.
- **Payment:** records linked to orders (`pending` / `completed` / `failed`).
- **RBAC:** Spatie roles & permissions; Filament CRUD for users, roles, permissions.
- **Admin:** Filament resources for all entities; panel access gated by `access_admin`.

## Capabilities

### New

- `commerce-catalog`, `commerce-cart`, `commerce-payment`, `rbac`, `translations`

### Modified

- `User`: HasRoles, panel access check
- Seeders assign admin role to demo user

## Impact

- New migrations, models, services, Filament resources, tests
