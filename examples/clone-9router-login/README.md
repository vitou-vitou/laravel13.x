# 9Router Login Clone (Laravel 13)

Copies the 9router dashboard login: one master password, a progressive lockout on failed attempts, and a guard that blocks remote logins while the default password is still in use. There is no `users` table auth. The session flag `dashboard_authenticated` is the only login state.

## Quick start

```bash
cd examples/clone-9router-login
composer setup        # install, .env, key:generate, migrate, npm build
php artisan serve     # http://127.0.0.1:8000 → redirects to /dashboard → /login
```

Log in from the same machine with `123456`. Run tests with `./vendor/bin/phpunit` or `php artisan test`.

## Configuration (`config/dashboard.php`)

| Env var | Config key | Default | Effect |
|---|---|---|---|
| `DASHBOARD_PASSWORD` | `dashboard.password` | empty | Bcrypt hash. When set, it's the only accepted password (`Hash::check`). |
| `INITIAL_PASSWORD` | `dashboard.initial_password` | `123456` | Plain-text password, used only while `DASHBOARD_PASSWORD` is empty. |
| `REQUIRE_LOGIN` | `dashboard.require_login` | `true` | `false` turns off `DashboardGuard` completely. |
| `AUTH_MODE` | `dashboard.auth_mode` | `password` | Only reported by `/api/auth/status`. Nothing else reads it. |

Generate a hash for `DASHBOARD_PASSWORD`. Single-quote it in `.env` because bcrypt hashes contain `$`:

```bash
php artisan tinker --execute="echo bcrypt('my-strong-password');"
# .env
DASHBOARD_PASSWORD='$2y$12$....'
```

## Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/` | Redirects to `/dashboard` |
| GET | `/login` | Blade login view (redirects to `/dashboard` if already logged in) |
| POST | `/login` | Form login (CSRF-protected web group) |
| POST | `/logout` | Form logout, redirects to `/login` |
| GET | `/dashboard` | Protected by the `dashboard.auth` middleware (`DashboardGuard`) |
| POST | `/api/auth/login` | JSON login, body `{"password": "..."}` |
| GET | `/api/auth/status` | `requireLogin`, `authMode`, `hasPassword`, `authenticated`, `displayName`, `loginMethod` |
| POST | `/api/auth/logout` | Invalidates the session, returns `{"success": true}` |

The API routes use the same cookie session as the web routes: `bootstrap/app.php` appends `EncryptCookies`, `AddQueuedCookiesToResponse` and `StartSession` to the `api` group. Exceptions for `api/*` are always rendered as JSON. The API group has no CSRF middleware.

### JSON login responses (`DashboardAuthController::login`)

| Status | When | Body |
|---|---|---|
| 200 | Password valid | `{"success": true, "mustChangePassword": false}` |
| 401 | Wrong password, not locked yet | `{"error", "remainingBeforeLock"}` |
| 403 | Correct default password from a non-local IP | `{"success": false, "error", "mustChangePassword": true}` |
| 429 | Locked out (checked before the password) | `{"error", "retryAfter", "resetHint"}` + `Retry-After` header |

Form logins get the same outcomes as validation errors on `password`, with session flashes (`retryAfter`, `resetHint`, `remainingBeforeLock`, `mustChangePassword`). The login view uses `retryAfter` to run its countdown timer.

`DashboardGuard` returns `401 {"error": "Unauthorized"}` for JSON and `api/*` requests, and redirects everything else to `/login`.

## Lockout rules (`app/Services/LoginLimiter.php`)

- State is stored per client IP in the default cache store under `dashboard_login_attempts:<md5(ip)>`, with a 1-hour TTL that restarts on every failure.
- The 5th consecutive failure locks the IP. The fail counter then resets, and the lock level goes up each time: **30 s → 2 min → 10 min → 30 min**. After that, every further lock is 30 min.
- Successful login clears the entry, so both fails and lock level reset. An hour with no failures also clears it, as long as no lock is still running.

## Remote default-password guard

Login is refused with 403 when all of these are true:

1. `DASHBOARD_PASSWORD` is empty, **and**
2. `INITIAL_PASSWORD` is unset or equal to `123456`, **and**
3. `$request->ip()` is not `127.0.0.1` or `::1`.

The limiter counts this as a success, so it doesn't add a failed attempt. To allow remote access, set `DASHBOARD_PASSWORD` (preferred) or a non-default `INITIAL_PASSWORD`.

## Pitfalls

- **`php artisan config:cache` breaks remote login.** Rule 2 above reads `env('INITIAL_PASSWORD')` straight in the controller, not through config. With a cached config `env()` returns `null`, so a custom `INITIAL_PASSWORD` is ignored and remote logins get 403. Use `DASHBOARD_PASSWORD` if you cache config.
- **Reverse proxies / tunnels.** Trusted proxies are not configured. Behind a proxy or tunnel running on the same host (Herd share, ngrok, nginx), every request can look like it comes from `127.0.0.1`. That turns off the remote guard, and all clients share one lockout bucket. Configure trusted proxies before exposing the app.
- **Multiple servers.** `.env.example` uses `CACHE_STORE=file`, so lockout state is per server. Use a shared store such as Redis if you run more than one instance.
- **The reset hint doesn't match this app.** `dashboard.reset_hint` talks about a "CLI -> Settings -> Reset Password to Default" flow. That comes from the original 9router, and this clone has no such command. To reset here, clear `DASHBOARD_PASSWORD` or `php artisan cache:clear` to lift a lockout.
- **The login page always shows "Default password is 123456".** The text is hard-coded in `resources/views/auth/login.blade.php`, even when you change `INITIAL_PASSWORD`.
