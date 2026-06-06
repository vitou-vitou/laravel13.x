# NEXT_SESSION — dashboard-v1

**App**: Analytics Dashboard  
**URL**: http://dashboard-v1.test  
**Tests**: 29/29 passing  
**MVP**: Complete (2026-06-06)

## What it is

Laravel 13 + Breeze — authenticated KPI dashboard with four metric cards and a recent orders table. Demo data from `DashboardMetricsService` (no DB metrics yet).

## Spec-Kit

`.specify/specs/001-dashboard-v1/` — all tasks T001–T009 checked

## Key files

| Path | Purpose |
|------|---------|
| `app/Services/DashboardMetricsService.php` | Demo KPIs + recent orders |
| `app/Http/Controllers/DashboardController.php` | Dashboard invokable controller |
| `resources/views/dashboard.blade.php` | KPI grid + orders table |
| `tests/Feature/DashboardTest.php` | Guest redirect, KPIs, table, welcome |

## Dev

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/dashboard-v1
npm run dev   # Vite; browse http://dashboard-v1.test
php artisan test
```

Register or use factory user; login → `/dashboard`.

## Do not redo

- Breeze scaffold, KPI service, dashboard view, feature tests

## Post-MVP only

- Chart.js / Livewire polling
- Database-backed metrics (Order model, aggregates)
- Filament admin, OpenSpec change orders
