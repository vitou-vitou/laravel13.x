# NEXT_SESSION — dashboard-v1

**App**: Analytics Dashboard + Commerce Admin  
**URL**: http://dashboard-v1.test  
**Admin**: http://dashboard-v1.test/admin  
**Shop**: http://dashboard-v1.test/shop  
**Tests**: 131/131 passing

## What it is

Laravel 13 + Breeze + Livewire 4 + Filament v5 + Reverb + Socialite + Spatie (permission, translatable).

- **Dashboard:** KPIs, charts, **Echo real-time** order updates (no poll)
- **Shop → Cart → Checkout:** line items, desktop alerts, admin email, **`NewOrderCreated` broadcast**
- **Admin:** Full CRUD + relation managers
- **Auth:** Email/password + optional **Google** / **Microsoft 365** / **GitHub** SSO (`GOOGLE_*`, `MICROSOFT_*`, `GITHUB_*` in `.env`)

## OpenSpec

All changes archived under `openspec/changes/archive/2026-06-06-*` (includes `add-theme-mode`, `add-github-sso`).

## Key paths

| Path | Purpose |
|------|---------|
| `app/Events/NewOrderCreated.php` | Broadcast on checkout |
| `routes/channels.php` | Private `orders` channel (admin/staff) |
| `resources/js/echo.js` | Laravel Echo + Reverb client |
| `resources/js/order-notifications.js` | Echo listener + desktop alerts |
| `app/Services/SsoAuthenticator.php` | Google + Microsoft + GitHub OAuth find/create/link |
| `app/Http/Controllers/Auth/SsoController.php` | SSO redirect + callback |

## Dev

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/dashboard-v1
php artisan migrate:fresh --seed
php artisan test
```

**Login:** `test@example.com` / `password`  

**Google / Microsoft / GitHub SSO (optional):** set client ID + secret in `.env`. Google rejects `.test` / `localhost` origins — use **ngrok** for OAuth testing only.

**GitHub:** create an OAuth App at https://github.com/settings/developers — callback `http://dashboard-v1.test/auth/github/callback` (local) or ngrok HTTPS for tunnel testing. Set `GITHUB_CLIENT_ID` and `GITHUB_CLIENT_SECRET`. Button hidden until both are set.

**Microsoft:** register an app in [Entra admin center](https://entra.microsoft.com/) → App registrations. Redirect URI: `https://YOUR-NGROK/auth/microsoft/callback`. `MICROSOFT_TENANT_ID=common` for work + personal accounts; use your tenant ID for single-tenant only.

### Google SSO via ngrok (local OAuth)

Keep **`APP_URL=http://dashboard-v1.test`** (fast Herd). Only **`GOOGLE_REDIRECT_URI`** uses the ngrok HTTPS URL.

```bash
# 1 — built assets (avoids Vite :5173 timeouts through ngrok)
npm run build

# 2 — tunnel with Herd host rewrite
ngrok http 127.0.0.1:80 --traffic-policy-file ngrok-traffic-policy.yml
```

`.env`:

```env
APP_URL=http://dashboard-v1.test
GOOGLE_REDIRECT_URI=https://YOUR-SUBDOMAIN.ngrok-free.app/auth/google/callback
```

Google Console: same ngrok URL for **origins** + **redirect**. Open **`https://YOUR-SUBDOMAIN.ngrok-free.app/login`** for SSO (not `.test`). After login you redirect back to `.test` — normal speed.

For GitHub OAuth via ngrok, keep `APP_URL=http://dashboard-v1.test` and set only:

```env
GITHUB_REDIRECT_URI=https://YOUR-SUBDOMAIN.ngrok-free.app/auth/github/callback
```

GitHub OAuth App callback URL must match. Open ngrok `/login` for the SSO flow.

**Why it felt slow:** `APP_URL` set to ngrok while browsing `.test` routed every link/asset through the tunnel; dev Vite (`:5173`) also times out from ngrok. ngrok itself adds ~300–500ms vs local.


Three terminals:

```bash
# 1 — Vite
npm run dev

# 2 — Reverb WebSocket server
php artisan reverb:start

# 3 — optional queue worker (if QUEUE_CONNECTION=database)
php artisan queue:work
```

`.env`: `BROADCAST_CONNECTION=reverb` (already set). Place an order from `/shop` in another tab while an admin watches `/dashboard` — KPIs and notifications update instantly.

## Theme

- **Light / Dark / Auto** toggle in nav (auth) and top-right on login/register
- Persists in `localStorage.theme` (`light` | `dark` | `system`) — shared with Filament `/admin`

## Next (optional)

- Customer receipt email on checkout
