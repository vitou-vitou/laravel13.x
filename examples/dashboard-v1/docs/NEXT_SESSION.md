# NEXT_SESSION — dashboard-v1

**App**: Analytics Dashboard + Commerce Admin  
**URL**: http://dashboard-v1.test  
**Admin**: http://dashboard-v1.test/admin  
**Shop**: http://dashboard-v1.test/shop  
**Tests**: 100/100 passing  

## What it is

Laravel 13 + Breeze + Livewire 4 + Filament v5 + Reverb + Socialite + Spatie (permission, translatable).

- **Dashboard:** KPIs, charts, **Echo real-time** order updates (no poll)
- **Shop → Cart → Checkout:** line items, desktop alerts, admin email, **`NewOrderCreated` broadcast**
- **Admin:** Full CRUD + relation managers
- **Auth:** Email/password + optional **Google** / **Microsoft 365** SSO (`GOOGLE_*`, `MICROSOFT_*` in `.env`)

## OpenSpec

All changes archived under `openspec/changes/archive/2026-06-06-*`.

## Key paths

| Path | Purpose |
|------|---------|
| `app/Events/NewOrderCreated.php` | Broadcast on checkout |
| `routes/channels.php` | Private `orders` channel (admin/staff) |
| `resources/js/echo.js` | Laravel Echo + Reverb client |
| `resources/js/order-notifications.js` | Echo listener + desktop alerts |
| `app/Services/SsoAuthenticator.php` | Google + Microsoft OAuth find/create/link |
| `app/Http/Controllers/Auth/SsoController.php` | SSO redirect + callback |

## Dev

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/dashboard-v1
php artisan migrate:fresh --seed
php artisan test
```

**Login:** `test@example.com` / `password`  

**Google / Microsoft SSO (optional):** set client ID + secret in `.env`. Google rejects `.test` / `localhost` origins — use **ngrok** for OAuth testing only.

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

## Next (optional)

- Customer receipt email on checkout
