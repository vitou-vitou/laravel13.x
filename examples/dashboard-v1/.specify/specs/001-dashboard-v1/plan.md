# Implementation Plan: Analytics Dashboard

**Feature**: `001-dashboard-v1`

## Tech Stack

| Layer | Choice |
|-------|--------|
| Framework | Laravel 13 |
| Auth | Laravel Breeze (Blade) |
| UI | Tailwind 4 + Breeze layouts |
| Tests | PHPUnit feature tests |
| Data | In-memory demo service (no DB models for metrics) |

## Architecture

```
routes/web.php
  └── auth middleware → DashboardController@index

app/Services/DashboardMetricsService.php
  └── getKpis(): array
  └── getRecentOrders(): array

resources/views/dashboard.blade.php
  └── KPI grid (4 cards)
  └── Recent orders table

tests/Feature/DashboardTest.php
  └── guest redirect, KPI presence, table headers, branding
```

## API Contracts

No public API. Dashboard is server-rendered only.

## Testing Strategy

1. Red: `DashboardTest` — guest redirect, authenticated KPI labels
2. Green: Controller + service + view
3. Extend: branding, table structure
