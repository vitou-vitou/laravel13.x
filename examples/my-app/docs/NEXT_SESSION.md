# Next session — my-app

**Updated:** 2026-06-03

## Status

| Item | Status |
|------|--------|
| Spec-Kit | `001-my_app` — **not started** (scaffold only) |
| Tests | Run `php artisan test` after first feature |

## Run

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x/examples/my-app
composer install
php artisan test
npm run dev
```

Browser: **http://127.0.0.1:8000/**

## Next agent steps

1. Edit `.specify/specs/001-my_app/spec.md` (what/why)
2. `plan.md` → `tasks.md`
3. Superpowers TDD implement
4. Update this file + `docs/SESSION_STATE.md` when MVP complete

## Pitfalls

- `php: command not found` → `export PATH="/d/laravel13.x/bin:$PATH"`
- UI on :5173 → use :8000 after `npm run dev`
