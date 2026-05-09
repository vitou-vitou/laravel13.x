# User guest dashboard (Laravel 13 + Filament 5)

Example app: Filament panel at `/guest` with a **Continue as guest** flow (session guard `guest`, `Guest` model). No email/password login in this demo.

## Requirements

- PHP **8.3+** (project runs on 8.3; newer PHP versions are generally fine)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) (for Vite / Filament frontend build)

## Setup

From this directory (`examples/user-guest-dashboard`):

```bash
composer install
cp .env.example .env
touch database/database.sqlite
```

Edit `.env`: use SQLite (default in `.env.example`):

- `DB_CONNECTION=sqlite`
- Comment out or remove MySQL `DB_HOST` / `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` if present.

Then:

```bash
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Run

```bash
php artisan serve
```

Open **http://127.0.0.1:8000/guest/login** (or the URL your console prints). Click **Continue as guest** to reach the dashboard. Use **Demo items** in the sidebar to create rows scoped to the current guest session.

## Tests

```bash
php artisan test
```

Feature coverage: continue-as-guest authentication and per-guest demo item isolation.
