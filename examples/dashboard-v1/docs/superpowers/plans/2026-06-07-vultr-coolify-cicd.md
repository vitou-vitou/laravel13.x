# Vultr + Coolify CI/CD Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Containerize the `dashboard-v1` Laravel + Filament app and deploy it to a Vultr droplet via Coolify, auto-building Docker on push with `main`→production and `staging`→staging.

**Architecture:** One multi-stage Docker image holds the whole app (php-fpm + nginx + node-built Vite assets). The same image runs as four services distinguished by a `CONTAINER_ROLE` env: `web` (supervisor → nginx + php-fpm), `reverb` (websocket), `worker` (queue), `scheduler` (cron). Postgres and Redis run as sibling containers with persistent volumes. Coolify (on the droplet) watches GitHub, builds the image, runs migrations, and swaps traffic through Traefik with auto-SSL.

**Tech Stack:** Laravel 13, PHP 8.3, Filament 5.6, Livewire 4.1, Laravel Reverb 1.10, Vite 8 / Tailwind 4, Postgres 16, Redis 7, Docker, Coolify, Traefik.

**All paths are relative to `examples/dashboard-v1/` unless stated otherwise.**

---

## File Structure

| File | Responsibility |
|------|----------------|
| `Dockerfile` | Multi-stage build: vendor deps → Vite assets → runtime image |
| `.dockerignore` | Keep build context small + secrets out of image |
| `docker/nginx.conf` | nginx vhost: serve `public/`, pass PHP to php-fpm |
| `docker/php.ini` | Production PHP tuning (opcache, no error display) |
| `docker/supervisord.conf` | Run nginx + php-fpm together in the `web` service |
| `docker/entrypoint.sh` | Wait for DB, cache config, role-gated migrate, exec role command |
| `docker-compose.yml` | 6 services (web, reverb, worker, scheduler, postgres, redis) + volumes |
| `.env.example` | Production-shaped env documentation (no secrets) |
| `routes/web.php` | Confirm/keep a health endpoint for Traefik |

---

## Task 1: Add `.dockerignore`

**Files:**
- Create: `.dockerignore`

- [ ] **Step 1: Create the file**

```
# deps + build artifacts (rebuilt in image)
/node_modules
/vendor
/public/build
/public/hot
public/hot.disabled

# local state + secrets
.env
.env.*
!.env.example
*.sqlite
*.sqlite-journal
database/*.sqlite
ruvector.db
.phpunit.result.cache

# local-only tooling
/_backups
.git
.github
.cursor
.specify
.impeccable
node_modules/.cache
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
ngrok-traffic-policy.yml
```

- [ ] **Step 2: Verify it is valid (no build yet)**

Run: `cat .dockerignore`
Expected: prints the content above, no error.

- [ ] **Step 3: Commit**

```bash
git add examples/dashboard-v1/.dockerignore
git commit -m "build: add .dockerignore for dashboard-v1 image"
```

---

## Task 2: nginx + PHP + supervisor config

**Files:**
- Create: `docker/nginx.conf`
- Create: `docker/php.ini`
- Create: `docker/supervisord.conf`

- [ ] **Step 1: Create `docker/nginx.conf`**

```nginx
server {
    listen 80 default_server;
    server_name _;
    root /var/www/html/public;
    index index.php;

    client_max_body_size 25m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_read_timeout 60;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
}
```

- [ ] **Step 2: Create `docker/php.ini`**

```ini
memory_limit = 256M
upload_max_filesize = 25M
post_max_size = 25M
max_execution_time = 60
expose_php = Off
display_errors = Off
log_errors = On

opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
```

- [ ] **Step 3: Create `docker/supervisord.conf`**

```ini
[supervisord]
nodaemon=true
user=root
logfile=/dev/stdout
logfile_maxbytes=0
pidfile=/run/supervisord.pid

[program:php-fpm]
command=php-fpm -F
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true

[program:nginx]
command=nginx -g "daemon off;"
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
```

- [ ] **Step 4: Verify files exist**

Run: `ls docker/`
Expected: `entrypoint.sh` (later) not yet; shows `nginx.conf  php.ini  supervisord.conf`.

- [ ] **Step 5: Commit**

```bash
git add examples/dashboard-v1/docker/nginx.conf examples/dashboard-v1/docker/php.ini examples/dashboard-v1/docker/supervisord.conf
git commit -m "build: add nginx, php.ini, supervisor config for container"
```

---

## Task 3: Entrypoint script (role-gated migrate + boot)

**Files:**
- Create: `docker/entrypoint.sh`

- [ ] **Step 1: Create `docker/entrypoint.sh`**

```bash
#!/usr/bin/env sh
set -e

ROLE="${CONTAINER_ROLE:-web}"

echo "[entrypoint] starting role=${ROLE}"

# Wait for Postgres (max ~60s)
if [ -n "${DB_HOST}" ]; then
  i=0
  until php -r "exit(@fsockopen(getenv('DB_HOST'), (int)getenv('DB_PORT')) ? 0 : 1);" 2>/dev/null; do
    i=$((i+1))
    if [ "$i" -ge 30 ]; then
      echo "[entrypoint] DB not reachable after 60s, continuing anyway"
      break
    fi
    echo "[entrypoint] waiting for DB ${DB_HOST}:${DB_PORT} ($i)"
    sleep 2
  done
fi

# Cache config/routes/views (safe for all roles, fast)
php artisan config:cache  || true
php artisan route:cache    || true
php artisan view:cache     || true

# Only the web role runs migrations + storage link (avoids races)
if [ "$ROLE" = "web" ]; then
  php artisan storage:link || true
  php artisan migrate --force || true
fi

case "$ROLE" in
  web)
    exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
    ;;
  reverb)
    exec php artisan reverb:start --host=0.0.0.0 --port=8080
    ;;
  worker)
    exec php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
    ;;
  scheduler)
    exec php artisan schedule:work
    ;;
  *)
    echo "[entrypoint] unknown role: $ROLE"
    exit 1
    ;;
esac
```

- [ ] **Step 2: Mark executable**

Run: `git update-index --chmod=+x examples/dashboard-v1/docker/entrypoint.sh 2>/dev/null; chmod +x examples/dashboard-v1/docker/entrypoint.sh`
Expected: no output, exit 0.

- [ ] **Step 3: Syntax check**

Run: `sh -n examples/dashboard-v1/docker/entrypoint.sh && echo OK`
Expected: `OK`

- [ ] **Step 4: Commit**

```bash
git add examples/dashboard-v1/docker/entrypoint.sh
git commit -m "build: add role-gated container entrypoint"
```

---

## Task 4: Dockerfile (multi-stage)

**Files:**
- Create: `Dockerfile`

- [ ] **Step 1: Create `Dockerfile`**

```dockerfile
# syntax=docker/dockerfile:1

# --- Stage 1: PHP/Composer dependencies ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev --no-scripts --no-interaction \
    --prefer-dist --optimize-autoloader --no-progress

# --- Stage 2: Frontend assets (Vite) ---
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
# vendor needed so any vite plugin resolving from vendor works
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# --- Stage 3: Runtime ---
FROM php:8.3-fpm-alpine AS runtime

# System deps + PHP extensions
RUN apk add --no-cache \
        nginx supervisor bash \
        postgresql-dev libpng-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-install \
        pdo_pgsql pgsql bcmath pcntl gd zip intl \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www/html

# App source
COPY . .
# Built vendor + assets from earlier stages
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Config files
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permissions for Laravel writable dirs
RUN chown -R www-data:www-data storage bootstrap/cache \
    && mkdir -p /run/nginx

EXPOSE 80 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
```

- [ ] **Step 2: Build the image locally**

Run (from `examples/dashboard-v1/`): `docker build -t dashboard-v1:test .`
Expected: build completes, ends with `naming to docker.io/library/dashboard-v1:test`. If `pdo_pgsql`/`redis` build fails, the apk deps line is the place to fix.

- [ ] **Step 3: Smoke-run the image (web role, no DB yet)**

Run: `docker run --rm -e CONTAINER_ROLE=web -e DB_HOST= -p 8088:80 dashboard-v1:test`
Then in another shell: `curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8088/up`
Expected: `200` (Laravel health route). Stop with Ctrl-C.

> If `/up` returns 404, do Task 5 first, then re-run this check.

- [ ] **Step 4: Commit**

```bash
git add examples/dashboard-v1/Dockerfile
git commit -m "build: add multi-stage Dockerfile for dashboard-v1"
```

---

## Task 5: Confirm health endpoint

**Files:**
- Modify: `bootstrap/app.php` (only if `/up` missing)
- Or Modify: `routes/web.php`

- [ ] **Step 1: Check for existing health route**

Run: `php artisan route:list | grep -i up`
Expected: a row for `GET|HEAD  up`. Laravel 11+ registers `/up` via `bootstrap/app.php` `->withRouting(health: '/up')`.

- [ ] **Step 2 (only if missing): Add health route to `routes/web.php`**

Append:

```php
Route::get('/up', fn () => response('OK', 200));
```

- [ ] **Step 3: Verify**

Run: `php artisan route:list | grep -i up`
Expected: `GET|HEAD  up` present.

- [ ] **Step 4: Commit (only if changed)**

```bash
git add examples/dashboard-v1/routes/web.php
git commit -m "feat: ensure /up health route for container healthcheck"
```

---

## Task 6: docker-compose.yml

**Files:**
- Create: `docker-compose.yml`

- [ ] **Step 1: Create `docker-compose.yml`**

```yaml
services:
  web:
    build:
      context: .
    image: dashboard-v1
    environment:
      CONTAINER_ROLE: web
    env_file: .env
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_started
    ports:
      - "8080:80"
    restart: unless-stopped

  reverb:
    image: dashboard-v1
    environment:
      CONTAINER_ROLE: reverb
    env_file: .env
    depends_on:
      - redis
    ports:
      - "8081:8080"
    restart: unless-stopped

  worker:
    image: dashboard-v1
    environment:
      CONTAINER_ROLE: worker
    env_file: .env
    depends_on:
      - redis
      - postgres
    restart: unless-stopped

  scheduler:
    image: dashboard-v1
    environment:
      CONTAINER_ROLE: scheduler
    env_file: .env
    depends_on:
      - redis
      - postgres
    restart: unless-stopped

  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: ${DB_DATABASE:-dashboard}
      POSTGRES_USER: ${DB_USERNAME:-dashboard}
      POSTGRES_PASSWORD: ${DB_PASSWORD:-secret}
    volumes:
      - pgdata:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${DB_USERNAME:-dashboard}"]
      interval: 5s
      timeout: 5s
      retries: 10
    restart: unless-stopped

  redis:
    image: redis:7-alpine
    command: ["redis-server", "--appendonly", "yes"]
    volumes:
      - redisdata:/data
    restart: unless-stopped

volumes:
  pgdata:
  redisdata:
```

- [ ] **Step 2: Create a local test env**

Run: `cp .env.example .env.docker.test && sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/' .env.docker.test`
(Then hand-set `DB_HOST=postgres`, `DB_PORT=5432`, `DB_DATABASE=dashboard`, `DB_USERNAME=dashboard`, `DB_PASSWORD=secret`, `REDIS_HOST=redis`, `SESSION_DRIVER=redis`, `CACHE_STORE=redis`, `QUEUE_CONNECTION=redis` — Task 7 documents these.)

This file is for local validation only; do not commit it (covered by `.dockerignore` pattern `.env.*`).

- [ ] **Step 3: Validate compose syntax**

Run: `docker compose config -q && echo OK`
Expected: `OK` (no schema errors).

- [ ] **Step 4: Commit**

```bash
git add examples/dashboard-v1/docker-compose.yml
git commit -m "build: add docker-compose for full multi-service stack"
```

---

## Task 7: Production-shaped `.env.example`

**Files:**
- Modify: `.env.example`

- [ ] **Step 1: Update DB / driver block in `.env.example`**

Replace the existing local values so the example documents the production shape. Set these keys:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://app.yourdomain.com

# Database (Postgres container/service)
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=dashboard
DB_USERNAME=dashboard
DB_PASSWORD=

# Redis-backed runtime
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=null

# Broadcasting via Reverb
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=ws.yourdomain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

> Keep `APP_KEY` empty in the example. Real secrets are set in the Coolify UI per environment.

- [ ] **Step 2: Verify no real secret leaked**

Run: `grep -E '^APP_KEY=' examples/dashboard-v1/.env.example`
Expected: `APP_KEY=` (empty) or absent. NOT a base64 value.

- [ ] **Step 3: Commit**

```bash
git add examples/dashboard-v1/.env.example
git commit -m "docs: document production env shape in .env.example"
```

---

## Task 8: Full local stack validation

**Files:** none (validation task)

- [ ] **Step 1: Bring up the stack**

Run (from `examples/dashboard-v1/`): `docker compose --env-file .env.docker.test up -d --build`
Expected: 6 containers created. `docker compose ps` shows `postgres` healthy, others `running`.

- [ ] **Step 2: Generate app key inside web (one-time for the test env)**

Run: `docker compose exec web php artisan key:generate --force`
Expected: `APP_KEY` set message. (In production Coolify holds this as a secret instead.)

- [ ] **Step 3: Verify HTTP + Filament**

Run: `curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/up`
Expected: `200`
Run: `curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8080/admin`
Expected: `200` or `302` (Filament login redirect), not `500`.

- [ ] **Step 4: Verify migrations ran (web role)**

Run: `docker compose exec postgres psql -U dashboard -d dashboard -c "\dt" | head`
Expected: Laravel tables listed (`migrations`, `users`, `permissions`, ...).

- [ ] **Step 5: Verify worker + reverb + scheduler alive**

Run: `docker compose logs --tail=20 worker reverb scheduler`
Expected: no crash loop; reverb prints it is listening on `0.0.0.0:8080`; worker idle waiting for jobs.

- [ ] **Step 6: Verify data persists across restart**

Run: `docker compose restart web && sleep 5 && docker compose exec postgres psql -U dashboard -d dashboard -c "select count(*) from migrations;"`
Expected: same non-zero count (volume persisted).

- [ ] **Step 7: Tear down**

Run: `docker compose down`
Expected: containers removed, volumes kept.

- [ ] **Step 8: Commit (validation notes only, if any docs added)**

No code change expected. If you captured notes, commit them; otherwise skip.

---

## Task 9: Coolify setup runbook (manual, documented)

**Files:**
- Create: `docs/superpowers/runbooks/coolify-setup.md`

> These steps run on the Vultr droplet + Coolify UI, not in code. The runbook makes them repeatable.

- [ ] **Step 1: Write the runbook**

````markdown
# Coolify Deploy Runbook — dashboard-v1

## 1. Provision droplet
- Vultr → Deploy → Cloud Compute, Ubuntu 24.04, 2 vCPU / 4 GB (~$24/mo).
- Add SSH key. Note public IP.

## 2. Install Coolify
SSH in, then:
```bash
curl -fsSL https://cdn.coollabs.io/coolify/install.sh | bash
```
Open `http://<droplet-ip>:8000`, create admin user.

## 3. Connect GitHub
- Coolify → Sources → GitHub → create GitHub App, install on the repo.
- This auto-adds the push webhook.

## 4. Create production application
- New Resource → Application → from the connected repo.
- Branch: `main`
- Build pack: **Dockerfile**
- Base directory / build context: `examples/dashboard-v1`
- Dockerfile location: `examples/dashboard-v1/Dockerfile`
- Add the Postgres + Redis as Coolify-managed databases OR deploy the `docker-compose.yml`.
  - Simplest: use Coolify "Docker Compose" build pack pointing at `examples/dashboard-v1/docker-compose.yml`.
- Domains: `app.yourdomain.com` (web :80), `ws.yourdomain.com` (reverb :8080).
- Healthcheck path: `/up`.

## 5. Secrets (production)
Set in Coolify → Environment Variables (mark as secret):
`APP_KEY` (generate: `php artisan key:generate --show`), `DB_PASSWORD`,
`REVERB_APP_ID/KEY/SECRET`, plus the production values from `.env.example`.

## 6. Create staging application
Repeat step 4 with branch `staging`, domains `staging.yourdomain.com` +
`ws-staging.yourdomain.com`, and its own secret set.

## 7. Enable auto-deploy
- In each app: turn on "Automatic Deployment" on push.
- Confirm webhook delivery in GitHub repo → Settings → Webhooks.

## 8. Backups
- Coolify → Postgres resource → Scheduled Backups → daily → S3 target
  (Vultr Object Storage credentials).

## 9. First deploy + smoke test
Push to `staging`, watch Coolify build logs, then run the smoke checklist
in the design spec section 6.
````

- [ ] **Step 2: Verify file**

Run: `ls examples/dashboard-v1/docs/superpowers/runbooks/`
Expected: `coolify-setup.md`

- [ ] **Step 3: Commit**

```bash
git add examples/dashboard-v1/docs/superpowers/runbooks/coolify-setup.md
git commit -m "docs: add Coolify deploy runbook for dashboard-v1"
```

---

## Task 10: Branch hygiene + final verification

**Files:** none

- [ ] **Step 1: Ensure `staging` branch exists**

Run: `git branch --list staging || git branch staging`
Expected: `staging` exists (create if missing). Push later when ready: `git push -u origin staging`.

- [ ] **Step 2: Confirm existing CI still green**

Run: `php artisan test`
Expected: existing PHPUnit suite passes (deploy work must not break app tests).

- [ ] **Step 3: Confirm no secrets staged**

Run: `git grep -nE 'APP_KEY=base64|DB_PASSWORD=.+' -- examples/dashboard-v1/.env.example examples/dashboard-v1/docker-compose.yml`
Expected: no matches.

- [ ] **Step 4: Final commit / push gate**

Push to `staging` only when Coolify is set up (Task 9), so the first webhook triggers a real deploy:

```bash
git push -u origin staging
```

---

## Self-Review Notes

- **Spec §3 topology** → Tasks 2,3,4,6 (web/reverb/worker/scheduler + pg/redis).
- **Spec §4 CI/CD flow** → Task 9 runbook (Coolify GitHub App, per-branch, auto-migrate, rollback) + Task 10 (staging branch/push).
- **Spec §5 repo changes** → Tasks 1–7 (Dockerfile, compose, nginx/php/supervisor, entrypoint, .env.example). `filament:assets` is covered: Filament v5 publishes assets via the Vite build + `public/build`; if a panel needs `php artisan filament:assets`, add it to `entrypoint.sh` web branch.
- **Spec §6 testing** → Task 8 (local full-stack validation) + runbook smoke checklist.
- **Spec §7 out-of-scope** respected (no previews, no managed DB, no LB).
- **Placeholder scan:** `yourdomain.com`, empty `APP_KEY`, `DB_PASSWORD=` are intentional user/secret values, documented as such.
- **Role consistency:** `CONTAINER_ROLE` values `web|reverb|worker|scheduler` match across entrypoint (Task 3) and compose (Task 6).
