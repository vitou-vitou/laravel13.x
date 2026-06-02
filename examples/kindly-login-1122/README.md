# kindly-login-1122

Standalone **session login** app (Laravel 180+ auth catalog). Built with **Spec-Kit + Superpowers**. External reviews use **Arena.ai only** — see [docs/ARENA_LOOP.md](docs/ARENA_LOOP.md).

**Resume:** [docs/NEXT_SESSION.md](docs/NEXT_SESSION.md)

## Stack

- Laravel 13, PHP 8.3+
- Laravel Breeze (Blade) — register, login, logout, dashboard
- SQLite (local)

## Spec-Kit

| Artifact | Path |
|----------|------|
| Constitution | `.specify/memory/constitution.md` |
| Spec | `.specify/specs/001-kindly-login/spec.md` |
| Plan | `.specify/specs/001-kindly-login/plan.md` |
| Tasks | `.specify/specs/001-kindly-login/tasks.md` |

**Do not** init OpenSpec on this greenfield repo. Use OpenSpec later for change orders only.

## Run

```bash
cd examples/kindly-login-1122
cp .env.example .env   # if needed
php artisan migrate
php artisan test
php artisan serve
```

Windows (Herd):

```bash
/c/Users/vitou/.config/herd/bin/php.bat artisan test
```

## Arena review (required by project policy)

1. Open https://arena.ai/ (not Grok)
2. Accept Terms once
3. Run Prompts A/B from `docs/ARENA_LOOP.md`
4. Save outputs to `docs/ARENA_REVIEW_SPEC.md` and `docs/ARENA_REVIEW_PLAN.md`

## Routes

- `/register`, `/login`, `POST /logout`
- `/dashboard` (auth)
