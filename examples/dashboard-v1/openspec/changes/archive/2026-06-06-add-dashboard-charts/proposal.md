## Why

KPI cards and a table show numbers but not trends. Chart.js on persisted order data makes revenue movement and status mix visible at a glance.

## What Changes

- Add Chart.js via npm and a dashboard-only Vite entry.
- Extend `DashboardMetricsService` with revenue trend (7-day) and order status breakdown payloads.
- Render two charts on `/dashboard` using server-provided JSON.
- Add tests for chart data and dashboard markup.

Non-goals: Livewire polling, real-time updates, Filament.

## Capabilities

### New Capabilities

- `dashboard-charts`: Server-built chart datasets and Chart.js rendering on the dashboard.

### Modified Capabilities

- `order-metrics`: Dashboard exposes chart-ready aggregates in addition to KPIs.

## Impact

- `package.json` (+ chart.js), `vite.config.js`, new `resources/js/dashboard-charts.js`
- `DashboardMetricsService`, `DashboardController`, `dashboard.blade.php`
- New/updated tests
