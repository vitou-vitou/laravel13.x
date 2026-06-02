# Implementation Plan: Kindly Login

**Branch**: `001-kindly-login` | **Date**: 2026-06-01 | **Spec**: [spec.md](./spec.md)

## Summary

Laravel 13 + **Laravel Breeze (Blade)** for session registration, login, logout, and a minimal dashboard. Arena.ai reviews spec/plan via `docs/ARENA_LOOP.md` (no Grok).

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13.x  
**Primary Dependencies**: laravel/breeze (dev), default PHPUnit  
**Storage**: SQLite, users table from Breeze  
**Testing**: `tests/Feature/Auth/*` (Breeze stubs + custom rate-limit test)  
**Target Platform**: Web (session)  
**Constraints**: Arena-only external AI; Spec-Kit + Superpowers workflow  

## Constitution Check

- [x] Auth invariants in tests  
- [x] TDD  
- [x] YAGNI — Breeze only  
- [x] Arena-only reviews documented  

## Project Structure

```text
examples/kindly-login-1122/
├── .specify/
├── docs/ARENA_LOOP.md
├── app/Models/User.php
├── routes/web.php          # Breeze auth + dashboard
├── tests/Feature/Auth/
└── resources/views/        # Breeze blades
```

## Routes (Breeze default)

- `GET/POST /register`, `/login`, `POST /logout`
- `GET /dashboard` (auth middleware)
- `GET /` → redirect logic per Breeze

## Arena integration

| Step | Tool |
|------|------|
| Spec review | Arena Prompt A → `docs/ARENA_REVIEW_SPEC.md` |
| Plan review | Arena Prompt B → `docs/ARENA_REVIEW_PLAN.md` |
| Execution | Cursor + Superpowers (tests) |

Use **non–Battle Mode** on arena.ai for single-model critique.

## Test matrix (MVP)

| Test | File |
|------|------|
| Register / login / logout | `tests/Feature/Auth/*` (Breeze) |
| Guest → login redirect | `AuthenticationTest` |
| Rate limit (5 failures) | `LoginSecurityTest` |
| Non-enumerating errors | `LoginSecurityTest` |
| Branding | `KindlyLoginBrandingTest` |
| Session regen + logout | `SessionSecurityTest` |

## Production checklist

- `APP_NAME=Kindly Login`, `APP_DEBUG=false`, `APP_URL` matches HTTPS origin
- `SESSION_SECURE_COOKIE=true` when TLS terminates at app or proxy is trusted
- Migrate `sessions` table; schedule `php artisan schedule:run` with session garbage collection / `session:gc` in production
- SQLite file permissions on shared hosting
- Post-MVP: CSP / `X-Frame-Options` on auth pages (Arena plan review 2026-06-01)

## Post-MVP

OpenSpec change orders for OAuth/2FA — not at init.
