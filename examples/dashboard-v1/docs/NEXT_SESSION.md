# NEXT_SESSION — dashboard-v1

**App**: Analytics Dashboard  
**URL**: http://dashboard-v1.test  
**Tests**: 40/40 passing  
**Post-MVP:** metrics, charts, Livewire polling complete

## What it is

Laravel 13 + Breeze + Livewire 4 — KPI cards and recent orders auto-refresh every 30s; Chart.js charts on initial load.

## OpenSpec (archive when ready)

- `add-order-metrics`
- `add-dashboard-charts`
- `add-livewire-polling`

## Key files

| Path | Purpose |
|------|---------|
| `resources/views/components/⚡dashboard-metrics.blade.php` | Livewire SFC, `wire:poll.30s` |
| `app/Services/DashboardMetricsService.php` | All metric/chart queries |
| `resources/js/dashboard-charts.js` | Static Chart.js on page load |

## Dev

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/dashboard-v1
php artisan migrate --seed
npm run dev
php artisan test
```

## Next (OpenSpec)

- Filament admin for orders CRUD
