# AGENTS.md

## Cursor Cloud specific instructions

This is the standard `laravel/laravel` skeleton (branch `13.x`) with a small custom
signed-URL demo in `routes/web.php` (`/signing` generates a signed URL, `/action`
validates `$request->hasValidSignature()`).

### Services
Single PHP web application. Dev tooling: PHP 8.3 + Composer (PHP deps), Node/npm + Vite
+ Tailwind v4 (frontend), SQLite (default DB), PHPUnit (tests), Pint (lint).

### Running / testing / building
Standard Laravel commands (see `composer.json` scripts and `.github/workflows/tests.yml`):
- Dev (all processes): `composer run dev` (runs `php artisan serve`, queue listener, `pail` logs, and `npm run dev` via concurrently).
- Serve only: `php artisan serve --host=127.0.0.1 --port=8000`.
- Frontend dev/build: `npm run dev` / `npm run build`.
- Tests: `php artisan test` (uses in-memory SQLite; no `.env`/DB setup needed).
- Lint: `./vendor/bin/pint --test` (add `--test` to check without rewriting files).

### Non-obvious caveats
- `laravel/pao` (dev dep) rewrites `pint` and `phpunit`/`artisan test` output into a single
  JSON line (agent-optimized). This is expected — a passing run looks like
  `{"tool":"phpunit","result":"passed",...}`, not the usual PHPUnit dots.
- Pint currently reports a pre-existing style failure in `routes/web.php` (the demo route).
  This is committed code, not an environment problem; do not "fix" it as part of setup.
- The committed `composer.lock` on this branch can lag `composer.json` (e.g. framework
  `^13.8`, `laravel/pao` added). If `composer install` fails with a lock/platform mismatch,
  run `composer update` to resync — the update script already falls back to this.
- `.env` and `database/database.sqlite` are gitignored and live in the VM snapshot. If a
  fresh checkout is missing them: `cp .env.example .env && php artisan key:generate &&
  touch database/database.sqlite && php artisan migrate --force`.
- Manual hello-world check the app is healthy: `curl -s localhost:8000/signing` returns a
  signed URL; opening it returns `{"message":"Signed route found"}`; stripping/altering the
  `signature` query param returns HTTP 403.
