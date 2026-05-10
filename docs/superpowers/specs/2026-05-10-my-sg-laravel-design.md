# Design: Singapore-focused Laravel example app (`examples/my-sg-laravel`)

**Status:** Approved (user confirmed “spec ok” 2026-05-10).  
**Source inspiration:** `examples/my-sg-app/fortunesoft-sg-nav/02-technology-services/services__laravel-development.md` (marketing copy trimmed; not a clone of third-party site).  
**Location:** New application root at `examples/my-sg-laravel/` (full `composer create-project` style tree, not a package inside the monorepo’s main Laravel app unless later refactored).

## Goal

Ship a **runnable** Laravel app that demonstrates “Laravel for Singapore” defaults: locale switch (English + Simplified Chinese), SGD presentation, PDPA/cookie notice placeholders, and a public “Laravel services” narrative page derived from the inspiration markdown (professional tone, no scraped CDN image spam).

## Non-goals

- Payment gateway integration (Stripe/PayNow) in v1 — stubs or copy-only mentions OK.
- Production deployment, CI for sub-app, or subdomain DNS.
- Legal-grade PDPA text — placeholder copy only; user replaces with counsel-reviewed text.
- Pixel-perfect recreation of Fortunesoft pages.

## User-visible behavior

- **Home:** Short positioning + links to Laravel service page + locale switcher.
- **Laravel service page:** Sections aligned to inspiration (benefits, industries teaser) — rewritten bullets, no broken hotlinked images; optional simple SVG or CSS icons only.
- **Locale:** URL prefix or session/cookie-backed locale (`en`, `zh_CN`) — pick one strategy and document in README (implementation plan detail).
- **Cookie banner:** Dismissible strip; stores consent flag (session or encrypted cookie); ties to placeholder PDPA page link.
- **Optional quote lead:** Simple form (name, email, message) storing to DB or logging only — **defer to implementation plan** if scope creeps; design allows either “form + migration” or “skip form v1”.

## Technical boundaries

- **Laravel:** Current stable (align with repo docs — Laravel 12.x if `composer create-project` defaults to it).
- **Frontend:** Blade + Tailwind + Laravel Breeze (Blade stack) unless Filament already required elsewhere for consistency — **prefer Breeze for public site**; add Filament only if admin for leads is in scope for same milestone (decision in plan phase).
- **Database:** SQLite default for local demo (`database/database.sqlite`) to reduce friction; `.env.example` documents MySQL for prod.

## Files / layout (high level)

- `examples/my-sg-laravel/` — standard Laravel tree (`app/`, `routes/web.php`, `resources/views/`, `lang/en`, `lang/zh_CN`, etc.).
- `examples/my-sg-laravel/README.md` — how to `cd`, `composer install`, `cp .env.example .env`, `key:generate`, `migrate` (if any), `artisan serve`.
- Do not move or delete existing `examples/my-sg-app/` content; new app is sibling.

## Testing (acceptance)

- `php artisan serve` from `examples/my-sg-laravel` serves home + service page without 500.
- Locale switch changes visible strings on at least home + one inner page.
- SGD example (e.g. formatted price) uses `en_SG` or project helper — no hardcoded US-only format.
- No external image requests required for core pages to render.

## Out of scope follow-ups

- Livewire/Inertia variants.
- Multi-tenant SaaS patterns.
- PayNow QR or GrabPay.

## Self-review (spec quality)

- Placeholder/TBD: optional quote form — explicitly deferred to implementation plan.
- Consistency: SQLite dev aligns with “easy clone”; migration path documented.
- Scope: single example app directory — bounded.
