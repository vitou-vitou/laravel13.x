# User Report — Laravel 13 + Filament Example

## What this is

A read-only admin panel that displays 200 seeded users with filters for country, city, device type, signup source, avatar presence, geo data, login activity, and signup date range.

---

## Setup

```bash
cd examples/user-report
composer install
cp .env.example .env
php artisan key:generate
# Edit .env: set DB_CONNECTION=sqlite, then create the file:
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=UserReportSeeder
composer require filament/filament:"^3.0" -W --ignore-platform-reqs
php artisan filament:install --panels  # choose panel ID 'admin', path 'admin'
php artisan make:filament-user
php artisan serve
```

---

## Access

Open your browser at: http://localhost:8000/admin

Log in with the Filament user you created in the last setup step.

---

## Filters available

- **Country** — filter by one of 10 countries (US, UK, KH, TH, SG, AU, JP, DE, FR, CA)
- **City** — filter by city name
- **Device type** — mobile / desktop / tablet
- **Signup source** — google / facebook / twitter / direct / referral
- **Has avatar** — users with or without a profile picture
- **Has geo data** — users with or without latitude/longitude
- **Has logged in** — users who have or have never logged in
- **Last login range** — date range filter on `last_login_at`
- **Signup date range** — date range filter on `created_at`

---

## Note

This panel is **read-only**. Create, edit, and delete actions are intentionally disabled. It is intended as a reporting and filtering demo only.
