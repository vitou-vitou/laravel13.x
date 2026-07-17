## Why

The dashboard MVP uses hardcoded demo arrays in `DashboardMetricsService`. Operators need KPIs and recent orders driven by persisted data so metrics reflect real (seeded) order activity and future chart work has a data layer.

## What Changes

- Add `Order` model with migration, factory, and seeder for demo orders.
- Refactor `DashboardMetricsService` to aggregate KPIs and recent rows from the database.
- Update dashboard feature tests to assert DB-backed output.
- Add unit tests for metric calculations.

Non-goals: Chart.js, Filament admin, Stripe, user-owned orders.

## Capabilities

### New Capabilities

- `order-metrics`: Persisted orders and dashboard KPI/recent-order queries.

### Modified Capabilities

- None (first OpenSpec change).

## Impact

- New `orders` table and `App\Models\Order`.
- `DashboardMetricsService` API unchanged for the view; implementation switches to Eloquent.
- `DatabaseSeeder` seeds sample orders for local demo.
