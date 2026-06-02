# Tasks: Kindly Login

## Phase 1 — Setup

- [x] T001 Laravel 13 skeleton in `examples/kindly-login-1122`
- [x] T002 `specify init` + constitution
- [x] T003 Install Laravel Breeze Blade stack

## Phase 2 — Spec-Kit artifacts

- [x] T004 spec.md, plan.md, tasks.md
- [x] T005 Arena Prompt A → `docs/ARENA_REVIEW_SPEC.md` (Sonnet 4.6, chat 019e83a3…)
- [x] T006 Arena Prompt B → `docs/ARENA_REVIEW_PLAN.md` (Sonnet 4.6, same chat)

## Phase 3 — Implement (Superpowers / TDD)

- [x] T007 Run Breeze migrations; verify `php artisan test` baseline
- [x] T008 Feature test: guest cannot access dashboard (Breeze `AuthenticationTest`)
- [x] T009 Feature test: register + login + logout happy path (Breeze auth tests)
- [x] T010 Feature test: login rate limiting + non-enumeration (`LoginSecurityTest`)
- [x] T011 Customize dashboard title to "Kindly Login"

## Phase 4 — Polish

- [x] T012 `docs/NEXT_SESSION.md` + README
- [x] T013 Full test run; update `.cursor/rules/specify-rules.mdc`
