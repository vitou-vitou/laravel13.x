## Decisions

| Topic | Choice |
|-------|--------|
| Poll interval | 30 seconds (`wire:poll.30s`) |
| Polled UI | KPI grid + recent orders table only |
| Charts | Outside Livewire component (no destroy/recreate) |
| Service | Reuse `DashboardMetricsService` |

## Testing

- `Livewire\DashboardMetricsTest` — renders KPIs, has poll directive
- Existing dashboard feature tests remain green
