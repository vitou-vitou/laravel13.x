# 9Router Login Clone (Laravel 13)

Replicates the single-master password authentication architecture, progressive lockout rate limiter, and remote access guard from `9router`.

## Architecture & Features

- **Single Master Password**: Defaults to `123456` or `INITIAL_PASSWORD`, or custom bcrypt hash in `DASHBOARD_PASSWORD`.
- **Progressive Brute-Force Lockout**:
  - Max 5 failed attempts before lockout.
  - Progressive lockout steps: `30s -> 2m -> 10m -> 30m`.
  - Auto-resets after 1 hour of inactivity.
  - Returns HTTP 429 with `Retry-After` header and reset hint.
- **Remote Default Password Guard (CVE-2026-56679 mitigation)**:
  - If default password (`123456`) is in use, remote clients cannot log in (HTTP 403 `mustChangePassword`).
- **Dashboard Guard**:
  - Protects `/dashboard` and redirects unauthenticated guests to `/login`.
- **Status Endpoint**:
  - `GET /api/auth/status` provides frontend with login requirement, auth mode, and authentication state.

## Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/login` | Blade login view with lockout timer & hint |
| POST | `/login` | Form login handler |
| POST | `/api/auth/login` | API login (`{"password": "..."}`) |
| GET | `/api/auth/status` | Current auth state & config |
| POST | `/api/auth/logout` | Invalidate session |
| GET | `/dashboard` | Protected dashboard |

## Running Tests

```bash
cd examples/clone-9router-login
./vendor/bin/phpunit
```
