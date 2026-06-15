# Next session — dynamic-warm-view-1906

**Updated:** 2026-06-13

## Status

| Item | Status |
|------|--------|
| Spec-Kit | `001-dynamic_warm_view_1906` — **not started** (scaffold only) |
| Herd | http://dynamic-warm-view-1906.test |
| Tests | `php artisan test` |

## Run

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x/examples/dynamic-warm-view-1906
php artisan test
npm run dev
```

Browser: **http://dynamic-warm-view-1906.test** (Herd — no `artisan serve` needed)

| Command | What it does |
|---------|----------------|
| `npm run dev` | Vite HMR (Herd serves PHP) |
| `npm run vite` | Vite only |

## Next agent steps

1. Edit `.specify/specs/001-dynamic_warm_view_1906/spec.md`
2. Superpowers TDD → implement
3. Update `docs/SESSION_STATE.md` when MVP complete

## Pitfalls

- `php: command not found` → `export PATH="/d/laravel13.x/bin:$PATH"`
- Opened :5173 instead of **http://dynamic-warm-view-1906.test**
- 500 Unsupported cipher → `./bin/fix-example-app-key dynamic-warm-view-1906` (bad APP_KEY / ANSI)
- Health check → `./bin/verify-example dynamic-warm-view-1906`
- OAuth/ngrok on Herd → traffic policy + `127.0.0.1:80` (not `ngrok http http://dynamic-warm-view-1906.test`) — `docs/EXAMPLE_DEV_LESSONS.md`

See `docs/EXAMPLE_DEV_LESSONS.md`.
