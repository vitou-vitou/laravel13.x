# Next session — clone-the-fb-nav

**Updated:** 2026-06-03

## What this is

Laravel 13 UI study: desktop Facebook top navigation (dark bar) from `docs/reference/fb-desktop-top-nav.png`. Spec-Kit + Superpowers greenfield; **no OpenSpec at init**.

## MVP status

| Item | Status |
|------|--------|
| Spec-Kit `001-fb-top-nav` | MVP complete |
| Tests | **6/6** (`FbTopNavTest` + `ExampleTest`) |
| Stack | Blade, Tailwind 4, Vite |

## Run (copy-paste)

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x/examples/clone-the-fb-nav
composer install
php artisan test
npm run dev
```

Browser: **http://clone-the-fb-nav.test** — tabs `/watch`, `/marketplace`, `/groups`, `/gaming`.

| Command | What it does |
|---------|----------------|
| `npm run dev` | Vite HMR (PHP via Herd) |
| `npm run vite` | Vite only :5173 — **not** the app |

**Git clone:** `.env` committed (dev `APP_KEY`). **PHP:** see `docs/WINDOWS_HERD_GITBASH.md` if `php: command not found`.

## Pitfalls (do not re-debug)

1. `php` missing → `export PATH="/d/laravel13.x/bin:$PATH"`
2. UI on :5173 → use :8000 after `npm run dev`
3. Wrong `APP_URL` in Vite banner → `php artisan config:clear`

## Key paths

| Path | Purpose |
|------|---------|
| `resources/views/components/fb-top-nav.blade.php` | Top bar |
| `config/fb-nav.php` | Tab metadata |
| `docs/DESIGN.md` | Color/layout guardrails |
| `.specify/specs/001-fb-top-nav/` | Spec-Kit artifacts |

## Do not redo

- Spec-Kit scaffold (`specify init`)
- Route-based active tab + feature tests
- Five primary tabs + utility aria-labels

## Post-MVP (OpenSpec or ad hoc)

- Pixel-tune icons against reference (Watch filled TV)
- Responsive collapse for center tabs on small screens
- Local avatar asset (remove dicebear CDN)
- Optional Arena/impeccable visual review per `docs/guides/ui-adoption-workflow/`

## Parent handoff

See `docs/SESSION_STATE.md` in repo root.
