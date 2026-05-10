# my-sg-laravel (Singapore-oriented Laravel 13 example)

Public marketing pages with English / Simplified Chinese locale switching, SGD formatting demo, and a placeholder PDPA/cookie consent strip.

## Setup

```bash
cd examples/my-sg-laravel
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
php artisan serve
```

Open http://127.0.0.1:8000 — use header links for **English** / **中文** and `/services/laravel`.

## Locale

Session key `locale`: values `en` or `zh_CN`. Switched via `GET /locale/{locale}`.

Strings live in `lang/en.json` and `lang/zh_CN.json`.

## PDPA

`/privacy` is placeholder copy only — replace before production.

## Production database

Switch `.env` from SQLite to MySQL using the commented block in `.env.example`.

## Tests

```bash
npm run build
php artisan test --filter=LocaleAndPagesTest
```

Built Vite assets (`public/build`) are required for views that call `@vite`; run `npm run build` after clone.
