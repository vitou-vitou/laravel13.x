# Next session — my-app-33

**Updated:** 2026-06-03

## Status

| Item | Status |
|------|--------|
| Spec-Kit | `001-my_app_33` — **not started** (scaffold only) |
| Herd | http://my-app-33.test |
| Tests | `php artisan test` |

## Run

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x/examples/my-app-33
php artisan test
npm run dev
```

Browser: **http://my-app-33.test** (Herd — no `artisan serve` needed)

| Command | What it does |
|---------|----------------|
| `npm run dev` | Vite HMR (Herd serves PHP) |
| `npm run vite` | Vite only |

## Next agent steps

1. Edit `.specify/specs/001-my_app_33/spec.md`
2. Superpowers TDD → implement
3. Update `docs/SESSION_STATE.md` when MVP complete

## Pitfalls

- `php: command not found` → `export PATH="/d/laravel13.x/bin:$PATH"`
- Opened :5173 instead of **http://my-app-33.test**
