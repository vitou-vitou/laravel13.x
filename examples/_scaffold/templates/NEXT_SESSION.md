# Next session — __SLUG__

**Updated:** __DATE__

## Status

| Item | Status |
|------|--------|
| Spec-Kit | `001-__FEATURE__` — **not started** (scaffold only) |
| Herd | __HERD_URL__ |
| Tests | `php artisan test` |

## Run

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x/examples/__SLUG__
php artisan test
npm run dev
```

Browser: **__APP_URL__** (Herd — no `artisan serve` needed)

| Command | What it does |
|---------|----------------|
| `npm run dev` | Vite HMR (Herd serves PHP) |
| `npm run vite` | Vite only |

## Next agent steps

1. Edit `.specify/specs/001-__FEATURE__/spec.md`
2. Superpowers TDD → implement
3. Update `docs/SESSION_STATE.md` when MVP complete

## Pitfalls

- `php: command not found` → `export PATH="/d/laravel13.x/bin:$PATH"`
- Opened :5173 instead of **__APP_URL__**
- 500 Unsupported cipher → `./bin/fix-example-app-key __SLUG__` (bad APP_KEY / ANSI)
- Health check → `./bin/verify-example __SLUG__`
- OAuth/ngrok on Herd → traffic policy + `127.0.0.1:80` (not `ngrok http http://__SLUG__.test`) — `docs/EXAMPLE_DEV_LESSONS.md`

See `docs/EXAMPLE_DEV_LESSONS.md`.
