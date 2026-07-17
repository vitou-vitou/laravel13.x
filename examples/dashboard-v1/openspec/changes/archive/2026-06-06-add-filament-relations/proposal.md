## Why

Admins editing a customer or category should see related orders/products inline without leaving the edit screen.

## What Changes

- **Customer → Orders** relation manager on customer edit
- **Category → Products** relation manager on category edit
- Filament tests for list + create via relation managers

## Capabilities

### New

- `filament-relations`: nested CRUD on parent edit pages

## Impact

- `CustomerResource`, `CategoryResource`, new RelationManager classes, tests
