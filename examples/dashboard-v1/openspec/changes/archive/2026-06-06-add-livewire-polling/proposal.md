## Why

KPIs and the orders table only update on full page reload. Livewire polling keeps the dashboard live without JavaScript refresh logic.

## What Changes

- Install Livewire v4.
- Extract KPI cards + recent orders into a `DashboardMetrics` Livewire component with `wire:poll.30s`.
- Leave Chart.js sections static (`wire:ignore` not needed — charts stay outside the Livewire component).

## Capabilities

### New Capabilities

- `livewire-polling`: Auto-refresh dashboard metrics every 30 seconds.

### Modified Capabilities

- `dashboard-charts`: Unchanged; charts remain server-rendered on initial load.

## Impact

- `composer.json`, app layout (Livewire assets)
- New `App\Livewire\DashboardMetrics`
- `dashboard.blade.php` simplified
