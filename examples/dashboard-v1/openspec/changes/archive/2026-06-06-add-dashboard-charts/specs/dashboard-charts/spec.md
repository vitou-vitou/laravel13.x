# dashboard-charts

## Requirements

### Requirement: Revenue trend chart

The dashboard SHALL display a line chart of paid revenue for each of the last seven days.

#### Scenario: Chart section visible

- **WHEN** an authenticated user views `/dashboard`
- **THEN** a "Revenue Trend" chart region is present

### Requirement: Status breakdown chart

The dashboard SHALL display a doughnut chart of order counts grouped by status.

#### Scenario: Status chart visible

- **WHEN** an authenticated user views `/dashboard`
- **THEN** an "Order Status" chart region is present

### Requirement: Data from database

Chart datasets SHALL be computed from the `orders` table, not hardcoded arrays.
