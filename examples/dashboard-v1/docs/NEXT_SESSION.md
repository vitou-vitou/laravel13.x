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

### SSO via ngrok (static dev domain — required for GitHub / Google / Microsoft)

**Problem:** Random ngrok URLs (`8262-203-…ngrok-free.app`) change every restart → OAuth apps reject callbacks.

**Fix:** Use your free **static dev domain** (one per ngrok account). Claim it once at https://dashboard.ngrok.com/domains.

Keep **`APP_URL=http://dashboard-v1.test`** (fast Herd). Only `*_REDIRECT_URI` values use the ngrok HTTPS URL.

```bash
# 1 — one-time: set domain + sync OAuth redirect URIs in .env
./scripts/sync-ngrok-oauth-env.sh --domain YOUR-NAME.ngrok-free.dev

# 2 — update Google / Microsoft / GitHub consoles with the printed URLs (once)

# 3 — start tunnel (builds assets, disables Vite hot file, opens /login)
./scripts/ngrok-vitou-dev-http.sh
```

Manual equivalent:

```bash
npm run build
ngrok http 127.0.0.1:80 --url https://YOUR-NAME.ngrok-free.dev --traffic-policy-file ngrok-traffic-policy.yml
```

Open **`https://YOUR-NAME.ngrok-free.dev/login`** for SSO (not `.test`). After login you redirect back to `.test` — normal speed.

**ERR_NGROK_3801 on first load (refresh works):** Usually **two ngrok endpoints** share the same static domain (`--pooling-enabled` or a stale cloud agent). One pool member is broken → ~50% of requests fail. Fix: open https://dashboard.ngrok.com/endpoints → **stop** every endpoint except your local `ngrok-vitou-dev-http.sh` tunnel. Do **not** use `--pooling-enabled`.

**Do not** use `ngrok http http://dashboard-v1.test` or dynamic URLs without `--url` — Herd 404 or OAuth mismatch.

**Vite + ngrok:** Stop `npm run dev` before SSO testing. Dev mode writes `public/hot` → assets load from `http://[::1]:5173`, which breaks on the HTTPS tunnel (`Vite manifest` / blank page after login). Use `./scripts/ngrok-vitou-dev-http.sh` (runs `npm run build` automatically).

**Why it felt slow:** `APP_URL` set to ngrok while browsing `.test` routed every link/asset through the tunnel; dev Vite (`:5173`) also times out from ngrok.


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
