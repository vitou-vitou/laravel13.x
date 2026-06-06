# Analytics Dashboard Constitution

## Core Principles

### I. Spec Before Code

`spec.md` = what/why. `plan.md` = stack. `tasks.md` gates implementation.

### II. Test-First (NON-NEGOTIABLE)

Feature tests before production code. Red → green → refactor.

### III. Simplicity (YAGNI)

Build only what the current spec requires.

## Technology Constraints

- PHP 8.3+, Laravel 13.x
- PHPUnit feature tests
- Spec-Kit at greenfield; OpenSpec post-MVP only

## Quality Gates

- `php artisan test` passes before MVP complete
- No secrets in git (except committed dev `APP_KEY` in this example folder)

## Governance

Spec-Kit + Superpowers. Never Spec-Kit + OpenSpec on the same feature.

**Version**: 1.0.0 | **Ratified**: 2026-06-06 | **Last Amended**: 2026-06-06
