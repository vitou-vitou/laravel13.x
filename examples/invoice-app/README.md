# Invoice App Example

Standalone Laravel 13 invoice management example. Built with Breeze auth, DomPDF.

## Setup

```bash
cd examples/invoice-app
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan serve
```

Register at `/register`, then visit `/customers` and `/invoices`.

## Test

```bash
php artisan test
```
