# OAuth desktop success (minimal demo)

Single-screen Laravel 13 app: Tailwind 4 + Vite, Dropbox-style “Successfully logged in” page.

**Route:** `GET /oauth/desktop/success` (name: `oauth.desktop.success`).  
**`/`** redirects there.

## Run

```bash
cd examples/oauth-desktop-success
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate --force
npm install && npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

Dev with hot reload: `composer run dev` (needs `npx concurrently` from npm deps).

## Tests

```bash
php artisan test
```
