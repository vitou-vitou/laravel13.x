# livewire-polling

## Requirements

### Requirement: Auto-refresh metrics

The dashboard SHALL refresh KPI cards and the recent orders table every 30 seconds without a full page reload.

#### Scenario: Poll attribute present

- **WHEN** an authenticated user views `/dashboard`
- **THEN** the metrics region includes Livewire polling every 30 seconds

#### Scenario: Metrics from database after poll

- **WHEN** a new order is created while the dashboard is open
- **THEN** a subsequent poll cycle reflects updated totals (verified in Livewire test)
