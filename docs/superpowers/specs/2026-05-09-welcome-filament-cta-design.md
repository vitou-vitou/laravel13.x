# Design: Welcome page CTA into Filament admin

**Status:** Approved (user confirmed 2026-05-09).  
**Scope:** Public welcome Blade only. No Livewire, no Filament UI components on welcome.

## Goal

Add one primary button on the welcome page that sends users into the Filament admin panel (`AdminPanelProvider`: panel id `admin`, path `admin`).

## Non-goals

- Rendering Filament `Action` / form APIs on the welcome page (wrong layer; welcome is not inside panel shell).
- New dependencies or global layout changes outside the two welcome templates listed below.

## Behavior

- **Unauthenticated:** Follow link to `/admin`; Filament shows its login flow.
- **Authenticated:** Same URL; Filament serves dashboard (or default landing) per panel config.
- **URL resolution:** Prefer a Filament-named route if stable in this repo (e.g. dashboard for `admin` panel). If named routes differ by Filament version, use `url('/admin')` with a one-line comment referencing `AdminPanelProvider` path. Implementation must not hardcode wrong path if panel path changes; adjust in one place.

## UI

- **Placement:** Below the body paragraph, above the footer.
- **Label:** `Open admin` (sentence case).
- **Style:** Primary filled control using existing welcome OKLCH theme tokens (`welcome-accent` / `welcome-fg` / backgrounds). Match focus-visible ring pattern used on the docs link. Minimum comfortable tap height (inline with prior polish).
- **Element:** `<a>` styled as button (semantic navigation, not `button` + JS).

## Files

- `resources/views/welcome.blade.php` (root Laravel app).
- `examples/basic-laravel-filamentphp/resources/views/welcome.blade.php`.

**Root vs example:** Root `composer.json` does not require Filament. Root welcome: wrap CTA in `@if (Route::has('filament.admin.pages.dashboard'))` (confirm exact route name with `php artisan route:list` for that app). Guard false → no CTA block. Example app always has panel: CTA always shown; `href` via stable named route or `url('/admin')` aligned with `AdminPanelProvider` path `admin`.

## Testing

- Click CTA from welcome: reaches panel or login without 404.
- Keyboard: focus ring visible; Enter activates link.
- Reduced motion: no new motion on CTA beyond existing page animations.

## Out of scope follow-ups

- Secondary “Documentation” link (already covered by inline doc link).
- i18n strings (single locale for now).
