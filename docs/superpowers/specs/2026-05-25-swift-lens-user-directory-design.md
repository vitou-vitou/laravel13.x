# swift-lens-4829 — User Directory Design

**Date:** 2026-05-25
**Type:** Read-only admin panel (no auth)
**Stack:** Laravel 13, Blade, Tailwind CSS, Alpine.js, SQLite

---

## Goal

Demo app that wraps the `sql_query_script.txt` user-filter logic into a browsable Laravel UI. Cloned from `examples/invoice-app` structure.

---

## Architecture

Single Laravel app under `examples/swift-lens-4829/`. No authentication. One controller, one model, one view.

```
examples/swift-lens-4829/
├── app/
│   ├── Http/Controllers/UserController.php
│   └── Models/User.php
├── database/
│   ├── migrations/xxxx_create_users_table.php
│   └── seeders/UserSeeder.php
├── resources/views/
│   ├── layouts/app.blade.php
│   └── users/index.blade.php
└── routes/web.php
```

---

## Routes

| Method | URI | Controller | Description |
|--------|-----|------------|-------------|
| GET | `/` | `UserController@index` | Filter + paginate users |

---

## Data Layer

### Migration: `users`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| username | varchar(100) | |
| email | varchar(150) | unique |
| country | varchar(100) | nullable |
| city | varchar(100) | nullable |
| device_type | varchar(20) | nullable — web/mobile/tablet |
| signup_source | varchar(20) | nullable — organic/referral/social/paid |
| avatar | varchar(255) | nullable — URL or path |
| last_login_at | timestamp | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### Seeder

200 rows via `fake()`. Distribution:
- ~60% have avatar
- ~70% active (last_login_at within 30 days)
- Countries: 6–8 varied (US, KH, TH, JP, FR, DE, AU, SG)
- Devices: web/mobile/tablet
- Sources: organic/referral/social/paid

---

## Controller: `UserController@index`

Accepts GET params matching filter sidebar. Builds Eloquent query using `when()` chains — no raw SQL.

Filters:
- `keyword` → LIKE on username, email, city, country
- `country` → exact match
- `city` → exact match
- `device_type` → exact match
- `signup_source` → exact match
- `has_avatar` → 1 = avatar IS NOT NULL, 0 = IS NULL
- `is_active` → 1 = last_login_at within active_days, 0 = outside/null
- `period` → day/week/month/year/custom (custom uses start_date + end_date on created_at)
- `start_date` / `end_date` → used when period = custom

Active threshold: 30 days (hardcoded constant).

Pagination: `paginate(20)->withQueryString()`.

---

## View: `users/index.blade.php`

Two-column layout:
- **Left sidebar** — filter form (GET, no JS required)
- **Right panel** — results table + pagination

### Filter sidebar inputs

- Text: keyword
- Select: country, city, device_type, signup_source, period
- Select: has_avatar (Any / Has avatar / No avatar)
- Select: is_active (Any / Active / Inactive)
- Date inputs: start_date, end_date — visible only when `period = custom` (Alpine `x-show`)
- Buttons: Apply (submit), Reset (link to `/`)

### Results table columns

`ID | Username | Email | Country | City | Device | Source | Avatar | Status | Joined`

- Status badge: green "Active" / gray "Inactive"
- Avatar: small thumbnail if present, dash if not
- Joined: formatted `created_at`

### Pagination

Laravel default pagination, `->withQueryString()` to preserve filters across pages.

---

## Geo Filter

Excluded. Seed data has no lat/lon. Not relevant to demo scope.

---

## Error Handling

- Invalid `period=custom` with missing dates: query falls back to no period filter (null guard in controller)
- Empty results: show "No users found" message in table body

---

## Testing

No automated tests (demo app). Manual verification: seed 200 users, apply each filter combination, confirm pagination preserves params.
