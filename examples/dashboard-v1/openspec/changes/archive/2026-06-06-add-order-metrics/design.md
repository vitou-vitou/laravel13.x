## Context

Post-MVP change on `examples/dashboard-v1`. Breeze auth and dashboard view exist; metrics are static arrays.

## Goals

- Store orders in SQLite (dev/test default).
- Compute KPIs from aggregates; list 10 most recent orders.
- Keep view/controller contracts stable.

## Decisions

| Topic | Choice | Rationale |
|-------|--------|-----------|
| Order shape | `customer_name`, `amount_cents`, `status`, `ordered_at` | Enough for dashboard table + revenue sums |
| Status values | `paid`, `pending`, `refunded` | Matches MVP table labels |
| Revenue KPI | Sum `amount_cents` where `status = paid` | Standard revenue definition |
| Active Users | `User::count()` | No order↔user FK yet; registered users proxy |
| Orders Today | Count where `ordered_at` is today (app timezone) | Literal label match |
| Conversion | `paid count / total count × 100` when total > 0, else 0% | Simple funnel proxy |
| Trends | Compare last 7 days vs prior 7 days per metric | Lightweight period-over-period |
| Recent orders | Latest 10 by `ordered_at` desc | Fits existing table |

## Testing

- Unit: `DashboardMetricsServiceTest` with factory data.
- Feature: `DashboardTest` creates orders, asserts rendered values.
