# Windows + Herd + Git Bash

Single reference so setup is not re-discovered every session.

## One-time shell setup

Add to `~/.bashrc`:

```bash
export PATH="/d/laravel13.x/bin:$PATH"
export PYTHONIOENCODING=utf-8
```

Restart Git Bash, then:

```bash
php -v   # should show PHP 8.4.x (Herd)
```

## Commands that always work (no PATH)

```bash
PHP=/c/Users/vitou/.config/herd/bin/php.bat
$PHP artisan test
$PHP /c/ProgramData/ComposerSetup/bin/composer.phar install
```

## Common mistakes

| Symptom | Cause | Fix |
|---------|--------|-----|
| `bash: php: command not found` | Herd not on PATH | `export PATH="/d/laravel13.x/bin:$PATH"` |
| Blank page / no nav at `:5173` | Only Vite running | Use `npm run dev` in clone-the-fb-nav; open **:8000** |
| Vite shows `APP_URL: http://localhost` | Stale config | Set `APP_URL=http://127.0.0.1:8000` in `.env`; `php artisan config:clear` |
| `No application encryption key` | Missing `.env` | `clone-the-fb-nav`: `.env` is in git; else `cp .env.example .env` |
| `npm run dev` stack overflow | Old `dev.sh` called `npm run dev` | Pull latest; `dev.sh` runs `npx vite` |

## Example apps (linked via `bin/new-example`)

```bash
./bin/new-example my-app-33 "My App 3344"
cd examples/my-app-33
npm run dev
# browser → http://my-app-33.test
```

`new-example` runs `herd link <slug> --update-env` so `.env` `APP_URL` matches the site.

## Herd `.test` sites

- **Auto:** `bin/new-example` runs `herd link`
- **Manual:** `cd examples/<slug> && herd link --update-env`
- Projects only under `%USERPROFILE%\Herd\` are auto-parked; `laravel13.x/examples/*` must be **linked** (scaffold does this)
