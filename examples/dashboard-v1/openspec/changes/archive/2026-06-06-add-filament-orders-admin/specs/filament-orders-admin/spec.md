## ADDED Requirements

### Requirement: Admin panel access

The system SHALL expose a Filament admin panel at `/admin` using the existing `web` guard and Breeze `User` model.

#### Scenario: Guest redirected

- **WHEN** an unauthenticated visitor requests `/admin/orders`
- **THEN** they are redirected to `/admin/login`

#### Scenario: Authenticated staff access

- **WHEN** an authenticated user visits `/admin/orders`
- **THEN** they see a list of orders from the database

### Requirement: Order CRUD

The system SHALL allow authenticated admin users to create, read, update, and delete `Order` records.

#### Scenario: Create order

- **WHEN** staff submits a valid order form (customer_name, amount_cents, status, ordered_at)
- **THEN** a new row is persisted and appears on the analytics dashboard data source

#### Scenario: Update order

- **WHEN** staff edits an existing order
- **THEN** changes are saved to the database

#### Scenario: Delete order

- **WHEN** staff deletes an order from the edit page
- **THEN** the order row is removed from the database
