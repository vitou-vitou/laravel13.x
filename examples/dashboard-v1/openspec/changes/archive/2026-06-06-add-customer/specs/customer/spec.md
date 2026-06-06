## ADDED Requirements

### Requirement: Customer entity

The system SHALL store customers in a `customers` table with `name` and optional unique `email`.

#### Scenario: Customer owns orders

- **WHEN** a customer has one or more orders
- **THEN** each order references the customer via `customer_id`

### Requirement: Customer admin CRUD

The system SHALL expose Filament CRUD for customers at `/admin/customers`.

#### Scenario: Create customer

- **WHEN** staff submits name (and optional email)
- **THEN** the customer is persisted and listed in admin

#### Scenario: Orders link to customers

- **WHEN** staff creates or edits an order
- **THEN** they select an existing customer or create one inline

## MODIFIED Requirements

### Requirement: Recent orders display

Recent orders on the dashboard SHALL show the linked customer's name (not a denormalized string column).
