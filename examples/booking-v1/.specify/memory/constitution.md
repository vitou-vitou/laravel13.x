# Booking-v1 Constitution

## Core Principles

### I. Domain Invariants First

Booking logic MUST enforce non-overlapping confirmed reservations per bookable resource. Holds MUST expire. Past slots MUST NOT be bookable. Cancellation policy MUST be enforced in the service layer and covered by tests.

### II. Test-First (NON-NEGOTIABLE)

Pest feature tests are written before implementation for each invariant and user story. Red → green → refactor. No feature is "done" without a failing test proved first.

### III. Thin HTTP, Thick Services

Controllers only authorize, validate, and delegate. State changes live in service classes (`BookSlot`, `ConfirmBooking`, `CancelBooking`). Policies enforce ownership.

### IV. Spec Before Code

`spec.md` defines what/why (no stack). `plan.md` defines how. `tasks.md` is the only implementation checklist. Deviations require updating artifacts first.

### V. Simplicity (YAGNI)

MVP scope: general appointment scheduling only. No payments, no multi-property hotel inventory, no external calendar sync in v1.

## Technology Constraints

- PHP 8.3+, Laravel 13.x
- SQLite for local dev; migrations portable to MySQL
- Pest for tests; target meaningful feature coverage on booking paths
- Session auth (Breeze) for customer and provider roles
- Filament v5 optional for admin in phase 2; web UI may be Blade + Livewire or minimal routes for MVP

## Quality Gates

- `php artisan test` passes before claiming complete
- Pint formatting on changed PHP files
- No secrets in repo; `.env` never committed

## Governance

This constitution overrides ad-hoc shortcuts. Amendments are documented in spec/plan with date. Spec-Kit is the SDD tool for this greenfield project; OpenSpec is reserved for post-MVP change orders only.

**Version**: 1.0.0 | **Ratified**: 2026-06-01 | **Last Amended**: 2026-06-01
