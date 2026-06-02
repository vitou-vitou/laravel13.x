# Kindly Login 1122 Constitution

## Core Principles

### I. Auth Invariants First

Credentials MUST be verified server-side. Failed logins MUST NOT reveal whether an email exists. Active sessions MUST invalidate on logout. Passwords MUST never be stored in plain text.

### II. Test-First (NON-NEGOTIABLE)

Feature tests cover register, login, logout, and failed login before UI polish. Red → green → refactor.

### III. Spec Before Code

`spec.md` = what/why only. `plan.md` = stack and routes. `tasks.md` gates implementation.

### IV. Arena AI for External Review Only

Design reviews and spec/plan critiques go through **Arena.ai** (cursor-ide-browser). **Do not** use Grok or other chat products for this project's review loop.

### V. Simplicity (YAGNI)

MVP: email + password session auth via Laravel Breeze. No OAuth, 2FA, or magic links in v1.

## Technology Constraints

- PHP 8.3+, Laravel 13.x, Laravel Breeze (Blade)
- SQLite local; session driver `database` or `file`
- PHPUnit feature tests (project default)
- Rate limit login: `throttle` middleware

## Quality Gates

- `php artisan test` passes before complete
- No secrets in git

## Governance

Spec-Kit is the SDD tool for this greenfield app. OpenSpec only after MVP for change orders. Superpowers = TDD + verification.

**Version**: 1.0.0 | **Ratified**: 2026-06-01 | **Last Amended**: 2026-06-01
