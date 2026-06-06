## Context

`add-order-metrics` landed DB-backed KPIs. Charts consume the same `orders` table.

## Decisions

| Topic | Choice |
|-------|--------|
| Library | Chart.js 4 via npm |
| Bundle | Separate Vite entry `dashboard-charts.js` loaded only on dashboard |
| Data transport | `<script type="application/json" id="dashboard-charts-data">` |
| Revenue chart | Line chart — paid revenue per day, last 7 days |
| Status chart | Doughnut — counts for paid / pending / refunded |
| Empty data | Zero-filled days and zero counts still render |

## Testing

- Unit: chart dataset methods
- Feature: canvas elements + JSON payload present on authenticated dashboard
