# order-metrics

## Purpose

Persist orders and expose dashboard KPIs and recent order rows from the database.

## Requirements

### Requirement: Order persistence

The system SHALL store orders with customer name, amount in cents, status, and ordered timestamp.

#### Scenario: Seed demo orders

- **WHEN** `php artisan db:seed` runs
- **THEN** at least four orders exist including customer "Jordan Lee"

### Requirement: KPI from database

The dashboard SHALL display Total Revenue, Active Users, Orders Today, and Conversion Rate computed from database state.

#### Scenario: Revenue reflects paid orders

- **WHEN** paid orders total $250.00 and the user views `/dashboard`
- **THEN** Total Revenue shows `$250.00`

#### Scenario: Recent orders from database

- **WHEN** an order exists for customer "Acme Corp"
- **THEN** the recent orders table includes "Acme Corp"

### Requirement: Empty state

When no orders exist, KPIs SHALL show zero-safe formatted values and the recent orders table SHALL render with no data rows.
