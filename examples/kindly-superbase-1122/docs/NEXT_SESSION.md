# Next session — kindly-superbase-1122

**Updated:** 2026-06-07

## Status

| Item | Status |
|------|--------|
| Spec-Kit | `001-kindly_superbase_1122` — **not started** (scaffold only) |
| Herd | http://kindly-superbase-1122.test |
| Tests | `php artisan test` |

## Run

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x/examples/kindly-superbase-1122
php artisan test
npm run dev
```

Browser: **http://kindly-superbase-1122.test** (Herd — no `artisan serve` needed)

| Command | What it does |
|---------|----------------|
| `npm run dev` | Vite HMR (Herd serves PHP) |
| `npm run vite` | Vite only |

## Next agent steps

1. Edit `.specify/specs/001-kindly_superbase_1122/spec.md`
2. Superpowers TDD → implement
3. Update `docs/SESSION_STATE.md` when MVP complete

## Pitfalls

- `php: command not found` → `export PATH="/d/laravel13.x/bin:$PATH"`
- Opened :5173 instead of **http://kindly-superbase-1122.test**
- 500 Unsupported cipher → `./bin/fix-example-app-key kindly-superbase-1122` (bad APP_KEY / ANSI)
- Health check → `./bin/verify-example kindly-superbase-1122`
- OAuth/ngrok on Herd → traffic policy + `127.0.0.1:80` (not `ngrok http http://kindly-superbase-1122.test`) — `docs/EXAMPLE_DEV_LESSONS.md`

See `docs/EXAMPLE_DEV_LESSONS.md`.
