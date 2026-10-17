# Debugbar Demo (Laravel 13 + laravel-debugbar)

Plain Laravel 13 skeleton with [`barryvdh/laravel-debugbar`](https://github.com/fruitcake/laravel-debugbar) ^4.4 installed, as a sandbox for trying the debug toolbar. There is no demo code beyond the default `GET /` welcome page. Add routes, queries and views yourself and watch them show up in the bar.

## Quick start

```bash
cd examples/debugbar-demo
composer install                 # debugbar is in require-dev, so don't use --no-dev
cp .env.example .env             # APP_DEBUG=true, DB_CONNECTION=sqlite
php artisan key:generate
php artisan migrate
php artisan serve                # http://127.0.0.1:8000, bar is injected at the bottom
```

`./dev.sh` is not included here, unlike other examples. Tests: `php artisan test` (only the two stock example tests).

## How the bar turns on

No `config/debugbar.php` is published, so the package defaults in `vendor/barryvdh/laravel-debugbar/config/debugbar.php` apply. `LaravelDebugbar::isEnabled()` turns the bar on when all of these are true:

1. `APP_DEBUG=true`, and
2. `APP_ENV` is **not** `testing` or `production` (unless `debugbar.force_allow_enable` is set), and
3. `DEBUGBAR_ENABLED` is unset (it then follows `APP_DEBUG`) or `true`, and
4. the request isn't running in the console.

That's why the bar never appears during `php artisan test` (`APP_ENV=testing`).

Useful env switches (package defaults):

| Env var | Default | Effect |
|---|---|---|
| `DEBUGBAR_ENABLED` | unset → follows `APP_DEBUG` | Force on/off |
| `DEBUGBAR_INJECT` | `true` | Auto-inject the bar into HTML responses |
| `DEBUGBAR_ROUTE_PREFIX` | `_debugbar` | Prefix for the bar's asset and storage routes |
| `DEBUGBAR_STORAGE_DRIVER` | `file` | Request history storage. With `file`, it's written to `storage/debugbar/` |
| `DEBUGBAR_COLLECTORS_*` | per collector | E.g. `DEBUGBAR_COLLECTORS_ROUTE=true`, `..._SESSION=true`, `..._EVENTS=true` (off by default); `..._DB`, `..._VIEWS`, `..._MAIL` (on by default) |

To customise beyond env vars, publish the config: `php artisan vendor:publish --provider="Fruitcake\LaravelDebugbar\ServiceProvider"`.

## Pitfalls

- **Never ship with `APP_DEBUG=true`.** The bar shows queries with their bindings, request data and environment details. Production is blocked by the environment check above, but a staging host with `APP_ENV=staging` and debug on will show it.
- **No bar on JSON / API responses.** Auto-injection only applies to HTML responses.
- **`storage/debugbar/` grows** with a JSON file per captured request while the bar is on.
