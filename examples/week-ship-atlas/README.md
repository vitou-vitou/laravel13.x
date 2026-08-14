# Week Ship Atlas

Laravel 13 example: turn the **Dev Style Atlas** week loop into a tiny web app in one sitting (1-week ship demo).

**Stack:** Laravel 13 · Blade · Tailwind 4 · SQLite · Pest/PHPUnit

**Formula taught:** Region × Company habit × Project type + practice evidence

## LDA (Quick)

| Layer | Choice |
|-------|--------|
| Logic | Mon–Sun board; create/edit/delete ship notes; verdict keep/drop/pending |
| Data | `ship_notes`: weekday, title, region, company_habit, project_type, practice, verdict |
| Architecture | Blade UI → `ShipNoteController` → Eloquent `ShipNote` → SQLite |
| Portal | None (standalone example) |
| Others | No auth MVP; seeded Week 1 notes; Feature tests |

## Setup

```bash
cd examples/week-ship-atlas
composer install
cp .env.example .env   # if needed; scaffold already has key
php artisan key:generate --force
php artisan migrate --seed
npm install
npm run build
```

**Windows Herd (preferred):**

```bash
herd link week-ship-atlas --update-env
npm run dev
```

Browse: `http://week-ship-atlas.test` (or `php artisan serve` → `http://127.0.0.1:8000`)

## Routes

| Method | Path | Action |
|--------|------|--------|
| GET | `/` | Week board |
| GET | `/notes/create` | New note form |
| POST | `/notes` | Store |
| GET | `/notes/{id}/edit` | Edit |
| PUT | `/notes/{id}` | Update |
| DELETE | `/notes/{id}` | Delete |

## Tests

```bash
php artisan test
```

## Spec stubs

`.specify/specs/001-week_ship_atlas/` — filled for this MVP.

## Out of scope (MVP)

- Login / multi-user
- Canvas embed
- Blog publish API
- Multi-week history charts

## Related

- Hub atlas: `docs/dev-style-atlas/README.md`
- Cursor canvas: `dev-style-atlas`
