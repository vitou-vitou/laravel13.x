# Next session — dashboard-v2

**Updated:** 2026-06-06

## Status

| Item | Status |
|------|--------|
| Spec-Kit | `001-dashboard_v2` — GitHub OAuth MVP |
| Auth | Breeze session + optional GitHub OAuth (Socialite) |
| Herd | http://dashboard-v2.test |
| Tests | `php artisan test` |

## Run

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x/examples/dashboard-v2
php artisan test
npm run dev
```

Browser: **http://dashboard-v2.test** (Herd — no `artisan serve` needed)

| Command | What it does |
|---------|----------------|
| `npm run dev` | Vite HMR (Herd serves PHP) |
| `npm run vite` | Vite only |

## GitHub OAuth setup

1. Create OAuth App: https://github.com/settings/developers  
   - Homepage URL: `http://dashboard-v2.test` (local) or ngrok HTTPS URL (OAuth testing)  
   - Callback URL: `http://dashboard-v2.test/auth/github/callback` (local) or `https://YOUR-SUBDOMAIN.ngrok-free.app/auth/github/callback`
2. Set in `.env`:
   - `GITHUB_CLIENT_ID`
   - `GITHUB_CLIENT_SECRET`
3. Visit `/login` — **Sign in with GitHub** appears when both are set.

### GitHub OAuth via ngrok (local callback)

GitHub accepts `http://localhost` for dev apps, but tunneling through Herd needs a **Host rewrite** or you get Herd’s “404 Site not found” (not Laravel).

Keep **`APP_URL=http://dashboard-v2.test`** for fast local browsing. Point only **`GITHUB_REDIRECT_URI`** at ngrok when testing OAuth.

```bash
# 1 — built assets (avoids Vite :5173 timeouts through ngrok)
npm run build

# 2 — tunnel with Herd host rewrite (from repo root or this directory)
ngrok http 127.0.0.1:80 --traffic-policy-file ngrok-traffic-policy.yml
```

`.env` for OAuth test:

```env
APP_URL=http://dashboard-v2.test
GITHUB_REDIRECT_URI=https://YOUR-SUBDOMAIN.ngrok-free.app/auth/github/callback
```

GitHub OAuth App: set **Authorization callback URL** to the same ngrok HTTPS callback. Open **`https://YOUR-SUBDOMAIN.ngrok-free.app/login`** for SSO (not `.test`).

**Do not** use `ngrok http http://dashboard-v2.test` — ngrok forwards the ngrok hostname and Herd has no matching site.

## Next agent steps

- Post-MVP: analytics widgets, Filament admin, OpenSpec change orders
- Update `docs/SESSION_STATE.md` when adding major features

## Pitfalls

- `php: command not found` → `export PATH="/d/laravel13.x/bin:$PATH"`
- Opened :5173 instead of **http://dashboard-v2.test**
- 500 Unsupported cipher → `./bin/fix-example-app-key dashboard-v2` (bad APP_KEY / ANSI)
- Health check → `./bin/verify-example dashboard-v2`

See `docs/EXAMPLE_DEV_LESSONS.md`.
