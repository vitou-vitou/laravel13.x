Github Login example (Laravel 13)

Steps to run locally:

1. cd D:\\laravel13.x\\examples\\github-login
2. Copy .env.example to .env and set values (GITHUB_CLIENT_ID, GITHUB_CLIENT_SECRET, GITHUB_REDIRECT)
3. Run `composer install` (requires PHP >= 8.3). If your local PHP < 8.3, install PHP 8.4+ or run on a compatible environment.
4. Require Socialite: `composer require laravel/socialite`
5. Generate app key: `php artisan key:generate`
6. Create sqlite database file if needed: `touch database\\database.sqlite` or create a DB and update .env
7. Run migrations: `php artisan migrate`
8. Serve: `php artisan serve`

Notes:
- The routes/web.php includes `/login/github` and `/login/github/callback` using Socialite.
- Add GITHUB_REDIRECT to match the callback URL configured in your GitHub OAuth App (e.g., http://localhost:8000/login/github/callback)
- A minimal Laravel skeleton was merged into this folder (artisan, bootstrap, public, config). Composer dependencies were installed here using --ignore-platform-reqs, but running Artisan commands requires a PHP runtime that satisfies Laravel's requirements (PHP >= 8.3/8.4). If `php artisan` fails locally, upgrade PHP and re-run `composer install` without --ignore-platform-reqs.
- To finish setup locally:
    1. Copy `.env.example` to `.env` and set GitHub keys.
    2. Run `composer install` on a machine with PHP 8.4+.
    3. Run `php artisan key:generate`, `php artisan migrate`, then `php artisan serve`.
