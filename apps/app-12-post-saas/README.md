# app-12-post-saas

Laravel 13 + Filament workspace-scoped SaaS demo: **workspaces** + **posts** (CRUD + datatable).

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan test
```

Filament admin: `/admin` (seed: `admin@example.com` / `password`).

Switch workspace under **Workspaces**, then manage **Posts** for the active workspace.
