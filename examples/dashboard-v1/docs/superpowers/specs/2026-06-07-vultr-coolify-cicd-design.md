# Vultr + Coolify CI/CD Deployment Design — dashboard-v1

**Date:** 2026-06-07
**Project:** `examples/dashboard-v1` (Analytics Dashboard)
**Goal:** Build real CI/CD that deploys this Laravel + Filament SaaS to a Vultr VPS, auto-building Docker on commit, with per-branch environments.

---

## 1. Context

`dashboard-v1` current stack:

- Laravel 13, PHP 8.3
- Filament 5.6 (admin panel), Livewire 4.1
- Laravel Reverb 1.10 (websockets, `BROADCAST_CONNECTION=reverb`)
- spatie/laravel-permission 8, spatie/laravel-translatable 6
- Frontend: Vite 8, Tailwind 4, Alpine, chart.js, laravel-echo
- Local-only drivers (must change for prod): `DB_CONNECTION=sqlite`, `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`

The app is multi-process (web + websocket + queue + scheduler). The deployment must run these as separate long-running services sharing one built image.

## 2. Decisions (locked during brainstorm)

| Topic | Decision |
|-------|----------|
| Deploy mechanism | **Coolify** (self-hosted PaaS) on a single Vultr droplet |
| Branch → env | **main → production**, **staging → staging** (two fixed envs only) |
| Feature branches | No deploy; existing `tests.yml` CI still runs |
| Database | **Postgres** in a container with persistent volume |
| Cache/Session/Queue | **Redis** in a container with persistent volume |
| Scaling later | Vertical resize of droplet; migrate Postgres to Vultr Managed DB when revenue justifies |

## 3. Architecture — Container Topology

Coolify deploys a `docker-compose` stack on the Vultr droplet. Traefik (bundled with Coolify) handles routing + automatic Let's Encrypt SSL.

```
Coolify (Vultr droplet, Ubuntu 24.04)
  Traefik            → SSL + routing + zero-downtime swap

  app image (built once from Dockerfile), run as 4 services:
    web        nginx + php-fpm        :80    (Filament, Livewire, HTTP)
    reverb     php artisan reverb:start :8080 (websockets / broadcast)
    worker     php artisan queue:work redis    (jobs: mail, broadcasts, heavy tasks)
    scheduler  php artisan schedule:work        (cron tasks)

  postgres    persistent volume: pgdata
  redis       persistent volume: redisdata
```

**Why 4 app processes:** Filament/Livewire are served by php-fpm (web). Reverb is a separate long-running websocket server and cannot share php-fpm. The queue worker processes background jobs. The scheduler replaces system cron. All four run the **same built image** with different commands.

**Domains (Traefik, auto-SSL):**

```
production:  app.yourdomain.com      + ws.yourdomain.com
staging:     staging.yourdomain.com  + ws-staging.yourdomain.com
```

**Droplet sizing:** start at 2 vCPU / 4 GB (~$24/mo) to comfortably host Filament + Reverb + Postgres + Redis + both envs. Resize up as load grows. Keep the app stateless (no local disk state) so scaling stays easy.

## 4. CI/CD Flow

### One-time setup

1. Create Vultr droplet (Ubuntu 24.04).
2. Install Coolify (single install script).
3. Coolify → connect GitHub via **GitHub App** (auto-configures webhook).
4. Create two Coolify applications, both with build context `examples/dashboard-v1`:
   - `prod` → watches branch `main`
   - `staging` → watches branch `staging`

### Auto-deploy loop

```
git push origin <main|staging>
      → GitHub App webhook → Coolify
Coolify pulls repo
      → docker build (Dockerfile): composer install + npm run build (Vite)
      → release command: php artisan migrate --force
      → start/replace services (web, reverb, worker, scheduler)
      → Traefik health check → swap traffic (zero-downtime)
      → old containers stop. Rollback button available on failure.
```

### Branch isolation

```
push main       → only prod redeploys
push staging    → only staging redeploys
push feature/*  → no deploy; existing tests.yml runs PHPUnit
```

The existing `tests.yml` GitHub Action remains the test gate (runs on push/PR). Coolify deploys only after merge to `main`/`staging`. Test and deploy stay separate.

## 5. Repo / Config Changes (in `examples/dashboard-v1`)

### New files

| File | Purpose |
|------|---------|
| `Dockerfile` | Multi-stage: composer deps → node/Vite build → php-fpm runtime |
| `docker-compose.yml` | Services: web, reverb, worker, scheduler, postgres, redis |
| `docker/nginx.conf` | nginx → php-fpm, serve `public/`, websocket proxy |
| `docker/php.ini` | Prod PHP (opcache on, `display_errors` off) |
| `docker/entrypoint.sh` | Wait for DB → `migrate --force` → `storage:link` → start process |
| `.dockerignore` | Exclude `node_modules`, `vendor`, `.env`, `ruvector.db`, `_backups` |

### Edits

- `.env.example` — add production-shaped variables (below).

### Env changes (local → prod drivers)

```diff
- DB_CONNECTION=sqlite
+ DB_CONNECTION=pgsql
+ DB_HOST=postgres
+ DB_PORT=5432
+ DB_DATABASE=dashboard
+ DB_USERNAME=<set>
+ DB_PASSWORD=<set>
- SESSION_DRIVER=file       → SESSION_DRIVER=redis
- CACHE_STORE=file          → CACHE_STORE=redis
- QUEUE_CONNECTION=sync      → QUEUE_CONNECTION=redis
  BROADCAST_CONNECTION=reverb (keep)
+ REVERB_HOST=ws.yourdomain.com
+ REVERB_PORT=443
+ REVERB_SCHEME=https
+ APP_ENV=production
+ APP_DEBUG=false
+ APP_URL=https://app.yourdomain.com
```

### Secrets

Entered in the Coolify UI per environment — never committed to git. Coolify injects them as runtime env. Production and staging each have their own set (separate DB password, `APP_KEY`, Reverb keys).

### Build steps (Filament + Vite specifics)

- Vite build inside Docker; publish Filament assets (`php artisan filament:assets`).
- `php artisan storage:link` in entrypoint.
- `php artisan config:cache route:cache view:cache` in build; opcache enabled.
- On deploy: `php artisan migrate --force` (and `filament:upgrade` if needed).

### Migrations

Run automatically via `entrypoint.sh` on each deploy (`migrate --force`).

### Backups

- Coolify scheduled Postgres dump → Vultr Object Storage (daily).
- Vultr volume snapshots for disaster recovery.

## 6. Testing / Validation

### Local build verify (before Vultr)

```
docker compose build        → image builds clean
docker compose up           → all 6 services healthy
visit localhost             → Filament login loads
migrate runs                → Postgres tables created
worker/reverb/scheduler     → no crash loops in logs
```

### Deploy verify (per env)

```
push staging → Coolify builds + deploys → staging URL loads
              Filament admin: login + CRUD
              websocket connects (realtime chart via Reverb)
              queue job runs (dispatch test job)
              SSL valid (https)
push main   → same checks on production URL
```

### Rollback test

Intentionally break a deploy → confirm Coolify rollback restores the previous release.

### Smoke checklist (per env)

```
[ ] https loads, cert valid
[ ] Filament login + 2 CRUD ops
[ ] Reverb websocket connected (browser devtools WS frame)
[ ] queue job processed (worker log)
[ ] scheduler tick (log)
[ ] DB persists across redeploy (data survives)
[ ] backup file appears in object storage
```

## 7. Out of Scope (YAGNI for now)

- PR/feature preview environments (add later when revenue funds a bigger droplet).
- Horizontal scaling / load balancer / Kubernetes.
- Vultr Managed Database (migration path noted; not initial).
- Multi-region.

## 8. Future Path

```
Stage 1 (now):   1 droplet, all services + pg/redis containers ($24/mo)
Stage 2:         resize droplet up as users grow
Stage 3:         split Postgres → Vultr Managed DB (swap DB_* env only)
Stage 4:         load balancer + multiple app servers when needed
```
