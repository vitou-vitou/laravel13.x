# Kindly E-Commerce 1122 Constitution

## Core Principles

### I. Commerce Invariants First

Prices and stock MUST be validated server-side at checkout. Cart totals MUST be computed from database prices, not client input. Orders MUST be owned by the authenticated user who placed them.

### II. Test-First (NON-NEGOTIABLE)

Feature tests cover catalog browse, cart mutations, checkout, and auth gates before UI polish. Red → green → refactor.

### III. Spec Before Code

`spec.md` = what/why only. `plan.md` = stack and routes. `tasks.md` gates implementation.

### IV. Arena AI for External Review Only

Design reviews and spec/plan critiques go through **Arena.ai** (cursor-ide-browser). **Do not** use Grok or other chat products for this project's review loop.

### V. Simplicity (YAGNI)

MVP + Phase 2 (complete): session auth, catalog, cart, checkout, coupons, stub pay (`pending`→`paid`), admin product CRUD.

Phase 3+ requires Arena Prompt C + `docs/PRE_ACTION_PLAN.md` gates. If Stripe: webhook signature + idempotency are mandatory. No multi-vendor until single-store payment + stock are hardened.

## Technology Constraints

- PHP 8.3+, Laravel 13.x, Laravel Breeze (Blade)
- SQLite local; session cart in session driver
- PHPUnit feature tests

## Quality Gates

- `php artisan test` passes before complete
- No secrets in git

## Governance

Spec-Kit is the SDD tool for this greenfield app. OpenSpec only after MVP for change orders. Superpowers = TDD + verification.

**Version**: 1.1.0 | **Ratified**: 2026-06-01 | **Last Amended**: 2026-06-01 (Phase 3 pre-action)
