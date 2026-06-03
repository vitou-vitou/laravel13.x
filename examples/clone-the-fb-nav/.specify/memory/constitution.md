# Clone the FB Nav — Constitution

## Core Principles

### I. Reference, Not Product

Visual study of Facebook desktop top navigation for learning UI adoption. No Meta APIs, no auth, no feed. Educational layout only.

### II. Test-First (NON-NEGOTIABLE)

Feature tests assert nav structure, accessibility labels, and active-tab state before pixel polish. Red → green → refactor.

### III. Spec Before Code

`spec.md` = what/why only. `plan.md` = stack and routes. `tasks.md` gates implementation.

### IV. UI Adoption Discipline

Follow `docs/DESIGN.md` and parent repo `docs/guides/ui-adoption-workflow/`. Extract one nav component; do not merge foreign repos wholesale.

### V. Simplicity (YAGNI)

MVP: static Blade + Tailwind 4 top bar, route-based active tab, placeholder content below. No Breeze, no OpenSpec at init.

## Technology Constraints

- PHP 8.3+, Laravel 13.x
- Blade + Tailwind 4 (Vite)
- PHPUnit feature tests
- SQLite default (unused in MVP)

## Quality Gates

- `php artisan test` passes before complete
- Icon controls have accessible names
- Active nav item exposes `aria-current="page"`

## Governance

Spec-Kit + Superpowers for greenfield. OpenSpec only post-MVP. Never combine Spec-Kit + OpenSpec on the same feature.

**Version**: 1.0.0 | **Ratified**: 2026-06-03 | **Last Amended**: 2026-06-03
