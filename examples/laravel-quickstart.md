# Laravel 12/13 — Quick Start & Min Spec

## Requirements

| Item | Min |
|---|---|
| PHP | 8.2+ (8.3 recommended) |
| Composer | 2.x |
| RAM | 256MB (512MB comfortable) |
| Disk | ~100MB |
| Database | SQLite (zero setup) |

## PHP Extensions Required

```
BCMath  Ctype  cURL  DOM  Fileinfo  JSON
Mbstring  OpenSSL  PCRE  PDO  Tokenizer  XML
```

## Install & Run (3 commands)

```bash
composer create-project laravel/laravel myapp
cd myapp
php artisan serve
```

Access: http://localhost:8000

## SQLite Default (Laravel 11+)

No MySQL/Postgres needed. SQLite is default.

```bash
php artisan migrate
php artisan serve
```

Done in ~2 min with warm Composer cache.

## Check PHP Version & Extensions

```bash
php -v
php -m
```
