# Example dev — lessons learned (self-study handoff)

**Purpose:** One page so agents and humans stop re-debugging the same Windows + Herd + Laravel example issues.

**Read when:** `bin/new-example`, 500 on `.test`, `php: command not found`, empty Vite page, encryption errors.

---

## Pitfall matrix

| Symptom | Root cause | Fix once |
|---------|------------|--------|
| `bash: php: command not found` | Herd PHP not on Git Bash PATH | `export PATH="/d/laravel13.x/bin:$PATH"` or `~/.bashrc` |
| Blank / wrong page at `:5173` | Only Vite running; Laravel is elsewhere | Open `APP_URL` from `.env` (e.g. `http://my-app-55.test`) |
| Vite banner `APP_URL: http://localhost` | Stale config | Match `.env`; `php artisan config:clear` |
| 500 **Unsupported cipher / incorrect key length** | `APP_KEY` contains ANSI `[33m` from `key:generate --show` | `./bin/fix-example-app-key <slug>` |
| `No application encryption key` | Missing `.env` on fresh clone | `new-example` commits `.env`; else `cp .env.example .env` + fix script |
| `artisan test` OK but browser 500 | Web uses bad `APP_KEY`; tests use `phpunit.xml` | Align both via `fix-example-app-key` |
| Site not found / connection refused | Herd not linked | `cd examples/<slug> && herd link <slug> --update-env` |
| Herd **404 Site not found** through ngrok (OAuth/webhook callback) | `ngrok http http://<slug>.test` forwards ngrok hostname as `Host`; Herd has no matching site | See **OAuth / webhooks via ngrok** below — traffic policy + `127.0.0.1:80` |

---

## OAuth / webhooks via ngrok (Herd)

**When:** GitHub/Google OAuth, Stripe webhooks, or any provider that needs a public HTTPS callback while PHP stays on Herd.

**Pattern (copy from `examples/dashboard-v1` or `dashboard-v2`):**

| Piece | Purpose |
|-------|---------|
| `ngrok-traffic-policy.yml` | Rewrites `Host` to `<slug>.test` so Herd routes correctly |
| `bootstrap/app.php` → `trustProxies(at: '*')` | Laravel trusts ngrok `X-Forwarded-*` for HTTPS URLs |
| `.env` | Keep **`APP_URL=http://<slug>.test`**; set provider **`*_REDIRECT_URI`** to ngrok HTTPS callback only |

```bash
cd examples/<slug>
npm run build   # avoid Vite :5173 through tunnel

# Static dev domain (free, one per account) — claim at https://dashboard.ngrok.com/domains
# dashboard-v1: ./scripts/sync-ngrok-oauth-env.sh --domain YOUR-NAME.ngrok-free.dev
ngrok http 127.0.0.1:80 --url https://YOUR-NAME.ngrok-free.dev --traffic-policy-file ngrok-traffic-policy.yml
```

**ERR_NGROK_3801 (first visit fails, refresh OK):** Two endpoints pooled on the same static domain — stop extras at https://dashboard.ngrok.com/endpoints. Do not use `--pooling-enabled`.

```env
APP_URL=http://<slug>.test
NGROK_DEV_DOMAIN=YOUR-NAME.ngrok-free.dev
GITHUB_REDIRECT_URI=https://YOUR-NAME.ngrok-free.dev/auth/github/callback
# or GOOGLE_REDIRECT_URI=…/auth/google/callback
```

Open **`https://YOUR-NAME.ngrok-free.dev/login`** for OAuth (not `.test`). **Do not** use random `ngrok http` without `--url` — URL changes every restart and breaks OAuth.

**Do not:** `ngrok http http://<slug>.test` — causes Herd 404, not Laravel.

**Reference:** `examples/dashboard-v2/docs/NEXT_SESSION.md` (GitHub), `dashboard-v1/docs/NEXT_SESSION.md` (Google).

---

## Golden paths

### New example (automated)

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x
./bin/new-example <slug> "Display Name"
./bin/verify-example <slug>
```

**Result:** `http://<slug>.test`, clean `APP_KEY`, `npm run dev` = Vite only.

### Daily dev

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/<slug>
php artisan test
npm run dev
# browser → grep APP_URL .env
```

### Agent must run before saying “done”

1. `php artisan test` — show pass count  
2. `./bin/verify-example <slug>` — APP_KEY + HTTP (if Herd up)  
3. Tell user **exact** `APP_URL`, not `:5173`

---

## Script map

| Script | When |
|--------|------|
| `bin/new-example` | Greenfield `examples/<slug>` |
| `bin/verify-example` | After scaffold or user reports 500 |
| `bin/fix-example-app-key` | Encryption / cipher errors |
| `bin/php`, `bin/composer` | Herd PHP on PATH |

---

## Scaffold invariants (do not regress)

`bin/new-example` must:

1. `key:generate --force --no-ansi` — **never** `--show` into `.env`  
2. `herd link <slug> --update-env`  
3. `cp .env` → `.env.example` **after** Herd updates `APP_URL`  
4. `dev.sh`: if `APP_URL` contains `.test` → Vite only  
5. End with `verify-example` (fail loud)

---

## SDD workflow (unchanged)

- Greenfield: **Spec-Kit + Superpowers** — not OpenSpec at init  
- Post-MVP: OpenSpec change orders  
- Do not re-scaffold MVPs in `docs/SESSION_STATE.md`  

---

## Related docs

- `docs/WINDOWS_HERD_GITBASH.md` — PATH + Herd  
- `docs/NEW_EXAMPLE_SCAFFOLD.md` — scaffold command  
- `.cursor/rules/windows-herd-gitbash.mdc` — agent always-on rule  

**Last consolidated:** 2026-06-06 (ngrok + Herd Host header, dashboard-v2 GitHub OAuth)
