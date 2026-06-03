# New Laravel example — scaffold

Creates `examples/<slug>/` with **Spec-Kit + Superpowers-ready** layout and **clone-the-fb-nav** dev ergonomics (no repeat debugging of PHP PATH, `.env`, or Vite :5173).

## Command

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x
./bin/new-example <slug> ["Display Name"]
```

Example:

```bash
./bin/new-example my-app-33 "My App 3344"
# → http://my-app-33.test  (slug drives Herd site name)
```

## Result on disk

| Path | Contents |
|------|----------|
| `examples/<slug>/` | Full Laravel 13 app |
| `.specify/` | Spec-Kit from `specify init` |
| `.specify/specs/001-<slug_with_underscores>/` | Stub `spec.md`, `plan.md`, `tasks.md` |
| `.specify/memory/constitution.md` | Starter constitution |
| `herd link <slug> --update-env` | Site at `http://<slug>.test` |
| `dev.sh` + `npm run dev` | Vite only when Herd linked; else :8000 + Vite |
| `.env` + `.env.example` | Dev `APP_KEY` + `APP_URL` aligned to Herd |
| `docs/NEXT_SESSION.md` | Agent handoff stub |
| Root `.gitignore` | `!examples/<slug>/.env` added |

## What scaffold does *not* do

- Does not implement product features (spec/tasks are stubs)
- Does not add Breeze/Sanctum/Stripe unless you specify later
- Does not update `docs/SESSION_STATE.md` until you mark MVP complete

## After scaffold

1. Choose scope (180+ catalog or brief)
2. Fill `spec.md` → `plan.md` → `tasks.md`
3. Superpowers TDD loop
4. `php artisan test` green → update `SESSION_STATE.md` + `NEXT_SESSION.md`

## Templates source

`examples/_scaffold/templates/` — edit once, all future `new-example` runs benefit.

## Verify after scaffold (required)

```bash
./bin/verify-example <slug>
```

Checks: `APP_KEY` format, `APP_URL` `.test`, tests, HTTP 200, `dev.sh`. Runs automatically at end of `new-example`.

## Fix broken site (500 — Unsupported cipher)

If `APP_KEY` contains `[33m` or looks colored, Git Bash captured ANSI from old scaffold:

```bash
./bin/fix-example-app-key my-app-55
php artisan config:clear
```

## Requirements

- Herd PHP (`bin/php` on PATH or default path in script)
- `specify` CLI (`uv tool install specify-cli …`)
- `composer`, `npm`, `node`

See also: `docs/WINDOWS_HERD_GITBASH.md`
