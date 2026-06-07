# 09 — Render Hosting Mastery

> Render is your production. Master its quirks. Your uptime depends on it.

---

## Why Render (The Senior's Case)

| Need | Render Wins Because |
|------|---------------------|
| Predictable billing | Flat per-service pricing, no surprise bills |
| Zero-ops | No SSH, no Nginx, no patches |
| Git-driven | Push to deploy, full CI pipeline support |
| First-class DBs | Managed Postgres + Redis with backups |
| Preview environments | Per-PR isolated apps for QA |
| Multi-service apps | Web + workers + cron in one dashboard |
| Heroku replacement | Cheaper, modern, IaC support |

When Render is wrong: high-scale spiky traffic (use Vapor/Lambda), need Laravel-native tooling (use Forge), hyper-regulated workloads (use bare AWS).

---

## The Render Service Types

### Web Service
HTTP-facing. Your Laravel app lives here. Auto-deploys on git push.

### Background Worker
Long-running process. Queue workers, websocket servers, ML inference daemons.

### Cron Job
Scheduled task. Daily backups, weekly reports.

### Static Site
SPA-only, no server. Render Nuxt/Next/Astro builds.

### Private Service
Internal-only HTTP. Microservices, internal APIs.

### Managed Postgres
Their hosted Postgres with backups, replicas.

### Key Value (Redis)
Managed Redis for cache, sessions, queues.

---

## The Laravel-on-Render Architecture

```
┌────────────────────────────────────────────────┐
│           Render (your production)             │
│  ┌─────────┐  ┌─────────┐  ┌────────────────┐  │
│  │  Web    │  │ Worker  │  │   Cron Job    │  │
│  │ Service │  │(Horizon)│  │(scheduler)    │  │
│  └────┬────┘  └────┬────┘  └───────┬───────┘  │
│       │            │                │          │
│       ▼            ▼                ▼          │
│  ┌────────────────────┐  ┌──────────────────┐ │
│  │ Postgres (managed) │  │  Redis (managed) │ │
│  └────────────────────┘  └──────────────────┘ │
└────────────────────────────────────────────────┘
                │
                ▼ files
         AWS S3 (external)
```

5 services, ~$50-80/mo total for small SaaS.

---

## `render.yaml` Infrastructure as Code

`render.yaml` at repo root defines everything. Version controlled. Reproducible.

```yaml
services:
  # Web service
  - type: web
    name: my-saas
    runtime: docker
    dockerfilePath: ./Dockerfile
    dockerContext: .
    plan: starter            # $7/mo
    region: oregon
    branch: main
    healthCheckPath: /healthz
    autoDeploy: true
    envVars:
      - key: APP_KEY
        generateValue: true
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: false
      - key: APP_URL
        value: https://my-saas.onrender.com
      - key: LOG_CHANNEL
        value: stack
      - key: LOG_LEVEL
        value: warning
      - key: DB_CONNECTION
        value: pgsql
      - key: DB_HOST
        fromDatabase:
          name: my-saas-db
          property: host
      - key: DB_PORT
        fromDatabase:
          name: my-saas-db
          property: port
      - key: DB_DATABASE
        fromDatabase:
          name: my-saas-db
          property: database
      - key: DB_USERNAME
        fromDatabase:
          name: my-saas-db
          property: user
      - key: DB_PASSWORD
        fromDatabase:
          name: my-saas-db
          property: password
      - key: REDIS_URL
        fromService:
          type: keyvalue
          name: my-saas-redis
          property: connectionString
      - key: QUEUE_CONNECTION
        value: redis
      - key: CACHE_STORE
        value: redis
      - key: SESSION_DRIVER
        value: redis
      - key: SENTRY_LARAVEL_DSN
        sync: false           # set manually in dashboard
      - key: STRIPE_KEY
        sync: false
      - key: STRIPE_SECRET
        sync: false

  # Queue worker
  - type: worker
    name: my-saas-worker
    runtime: docker
    dockerfilePath: ./Dockerfile
    dockerContext: .
    plan: starter
    region: oregon
    branch: main
    autoDeploy: true
    dockerCommand: php artisan horizon
    envVars:
      - fromGroup: my-saas-shared

  # Scheduler (one-shot cron)
  - type: cron
    name: my-saas-scheduler
    runtime: docker
    dockerfilePath: ./Dockerfile
    dockerContext: .
    plan: starter
    region: oregon
    schedule: "* * * * *"
    dockerCommand: php artisan schedule:run
    envVars:
      - fromGroup: my-saas-shared

envVarGroups:
  - name: my-saas-shared
    envVars:
      - key: APP_KEY
        generateValue: true
      - key: APP_ENV
        value: production
      # ... shared with web/worker

databases:
  - name: my-saas-db
    plan: starter            # $7/mo
    region: oregon
    databaseName: app
    user: app
    postgresMajorVersion: 16
```

Commit. Render reads it on first connect and creates everything.

---

## The Production Dockerfile

Senior-grade Dockerfile for Laravel on Render:

```dockerfile
# syntax=docker/dockerfile:1.7

# --- Stage 1: Build PHP deps ---
FROM composer:2 AS composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# --- Stage 2: Build frontend assets ---
FROM node:22-alpine AS node
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# --- Stage 3: Final runtime image ---
FROM php:8.4-fpm-alpine AS runtime

# System deps
RUN apk add --no-cache \
        nginx \
        bash \
        curl \
        supervisor \
        postgresql-dev \
        oniguruma-dev \
        libzip-dev \
        libpng-dev \
        libwebp-dev \
        icu-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        bcmath \
        mbstring \
        zip \
        gd \
        intl \
        opcache \
        pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis

# PHP config
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini
COPY docker/www.conf /usr/local/etc/php-fpm.d/www.conf

# Nginx config
COPY docker/nginx.conf /etc/nginx/nginx.conf

# Supervisor (run php-fpm + nginx in one container)
COPY docker/supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/html

# Copy app code
COPY . .
COPY --from=composer /app/vendor ./vendor
COPY --from=node /app/public/build ./public/build

# Optimize Composer
RUN composer dump-autoload --optimize --no-dev

# Laravel optimizations
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
```

### `docker/nginx.conf`

```nginx
worker_processes auto;
error_log /dev/stderr warn;
pid /tmp/nginx.pid;

events { worker_connections 1024; }

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    log_format json escape=json '{'
        '"time":"$time_iso8601",'
        '"remote_addr":"$remote_addr",'
        '"request":"$request",'
        '"status":$status,'
        '"bytes_sent":$bytes_sent,'
        '"http_referer":"$http_referer",'
        '"http_user_agent":"$http_user_agent",'
        '"request_time":$request_time'
    '}';

    access_log /dev/stdout json;

    sendfile on;
    keepalive_timeout 65;
    client_max_body_size 50M;
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;

    server {
        listen 80 default_server;
        server_name _;
        root /var/www/html/public;
        index index.php;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            try_files $uri =404;
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_split_path_info ^(.+\.php)(/.+)$;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $realpath_root;
        }

        location ~ /\.(?!well-known) { deny all; }

        # Render health
        location = /healthz {
            access_log off;
            try_files $uri /index.php?$query_string;
        }
    }
}
```

### `docker/supervisord.conf`

```ini
[supervisord]
nodaemon=true
logfile=/dev/null
logfile_maxbytes=0
pidfile=/tmp/supervisord.pid

[program:php-fpm]
command=php-fpm -F
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=10

[program:nginx]
command=nginx -g 'daemon off;'
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
autorestart=true
priority=20
```

### `docker/php.ini`

```ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 60
expose_php = Off
date.timezone = UTC
```

### `docker/opcache.ini`

```ini
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
opcache.interned_strings_buffer = 32
opcache.fast_shutdown = 1
opcache.enable_cli = 0
```

This stack delivers 50-100ms response times on $7/mo Starter.

---

## Release Command (Migrations)

Render lets you run a command BEFORE traffic is routed to a new deploy.

In Render dashboard → Settings → Build & Deploy → Pre-Deploy Command:

```bash
php artisan migrate --force --isolated && \
php artisan storage:link && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
php artisan event:cache
```

`--isolated` prevents two instances racing the migration.
`--force` runs in production (Laravel asks otherwise).

If pre-deploy fails, deploy is blocked. Old version stays live. Safe.

---

## The Render Postgres Quirks

### SSL Required

```env
DB_SSLMODE=require
```

Laravel + Postgres SSL works out of the box. But you may need to set:
```php
// config/database.php
'pgsql' => [
    'sslmode' => env('DB_SSLMODE', 'require'),
],
```

### Connection Limits

| Plan | Max Connections |
|------|-----------------|
| Starter | 97 |
| Standard | 197 |
| Pro | 397 |
| Custom | varies |

With many workers, you'll hit this. Solutions:
- Reduce `DB_POOL_SIZE`
- Use PgBouncer
- Move to higher plan

### Backup Frequency

| Plan | Backup Cadence |
|------|----------------|
| Starter | Daily |
| Standard | Daily + manual |
| Pro | PITR (point-in-time) |

PITR requires Pro tier ($95+/mo). For most apps, daily is fine.

### Read Replicas

Available on Standard+. Use for heavy SELECTs:
```env
DB_READ_HOST=replica.render.com
DB_WRITE_HOST=primary.render.com
```

### IP Allowlisting

Render Postgres has an internal hostname for services in same region. Use that, not public:
```env
DB_HOST=dpg-xxxx.oregon-postgres.render.com   # external
DB_HOST=dpg-xxxx-a                            # internal, faster, no IP issues
```

Internal hostname = no egress fees, lower latency, no firewall config.

---

## Render Redis (Key Value)

Use `REDIS_URL` for connection:
```env
REDIS_URL=redis://default:password@host:6379
```

`config/database.php` reads it:
```php
'redis' => [
    'default' => [
        'url' => env('REDIS_URL'),
    ],
],
```

Same internal vs external rules apply.

---

## Health Checks

Render hits `healthCheckPath` to determine if app is alive. Failed checks → restart.

```php
Route::get('/healthz', function () {
    try {
        DB::select('SELECT 1');
        Redis::ping();
        return response()->json(['status' => 'ok']);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'fail', 'error' => $e->getMessage()], 503);
    }
});
```

Watch out: aggressive health checks can hammer DB. Keep it cheap.

---

## Scaling

### Vertical (Bigger Box)

Render dashboard → Service → Instance Type → upgrade.

| Plan | RAM | CPU | $/mo |
|------|-----|-----|------|
| Starter | 512MB | 0.5 | $7 |
| Standard | 2GB | 1 | $25 |
| Pro | 4GB | 2 | $85 |
| Pro Plus | 8GB | 4 | $175 |
| Pro Max | 16GB | 8 | $350 |

### Horizontal (More Boxes)

Render dashboard → Service → Scaling → Number of Instances.

| Plan | Auto-scaling |
|------|--------------|
| Starter | Manual |
| Standard | Manual or auto |
| Pro+ | Auto-scaling based on CPU/RAM |

Auto-scaling = set min and max instances, scales based on CPU > 70%.

### Workers Scale Independently

Web service stays 2 instances. Worker scales to 8 during heavy email blasts. Adjust per service.

---

## Preview Environments

`render.yaml`:
```yaml
services:
  - type: web
    name: my-saas
    previewsEnabled: true
    previews:
      generation: automatic
```

Every PR gets its own:
- Web service
- Postgres DB
- Redis instance
- Unique URL

QA on real infrastructure before merge. Free with paid plans.

---

## Custom Domains & SSL

1. Render dashboard → Service → Custom Domains
2. Add `app.yourdomain.com`
3. Add CNAME at DNS provider pointing to Render's URL
4. Render auto-provisions Let's Encrypt SSL within 5 min

Wildcards (`*.yourdomain.com`) need DNS-01 challenge (more complex). Render guides you.

---

## Render CLI

```bash
# Install
brew install render

# Login
render login

# Tail logs
render logs my-saas -f

# Run one-off command
render run my-saas -- php artisan tinker

# Trigger deploy
render deploys create my-saas
```

Powerful for incident response. SSH-like without the SSH.

---

## SSH (Shell Access)

Render Service → Shell tab → terminal in your container.

```bash
php artisan tinker
php artisan queue:retry all
php artisan cache:clear
```

For one-off prod debugging. Don't make changes here — they vanish on next deploy.

---

## The Ephemeral Filesystem Trap

**Every file written to disk vanishes on redeploy.**

Bad:
```php
Storage::disk('local')->put("uploads/{$user->id}/avatar.jpg", $file);
```
Avatar gone after next deploy.

Good:
```php
Storage::disk('s3')->put("uploads/{$user->id}/avatar.jpg", $file);
```
Files persist on S3.

Render disk = scratch space only. Cache, logs (rotating), build artifacts. Nothing user-generated.

### Render Disks (Persistent Volumes)

For state that NEEDS to live on the machine:
```yaml
services:
  - type: web
    disk:
      name: data
      mountPath: /var/data
      sizeGB: 10
```

`$0.25/GB/mo`. Use sparingly. S3 is cheaper for files.

Use disks for:
- SQLite DB (small apps)
- Persistent uploads when S3 is overkill
- ML model weights

Don't use disks if you scale horizontally — each instance has its own disk.

---

## Logging on Render

Render aggregates `stdout`/`stderr` into the Logs tab.

Laravel config:
```php
// config/logging.php
'channels' => [
    'stderr' => [
        'driver' => 'monolog',
        'level' => env('LOG_LEVEL', 'debug'),
        'handler' => StreamHandler::class,
        'formatter' => env('LOG_STDERR_FORMATTER'),
        'with' => ['stream' => 'php://stderr'],
    ],
    'stack' => [
        'driver' => 'stack',
        'channels' => ['stderr', 'sentry'],
    ],
],
```

`LOG_CHANNEL=stack` → logs go to Render dashboard + Sentry.

### Log Streaming

Render → Log Streams → Add Datadog/Logtail/Papertrail.

Don't keep logs only in Render. They rotate. External logging = compliance.

---

## Render Gotchas

### Build Times

First deploy of a Docker image: 5-10 min.
Subsequent deploys with layer caching: 2-3 min.

Speed up:
- Multi-stage Dockerfile
- Mount Composer cache (`RUN --mount=type=cache,target=/tmp/composer`)
- Order layers: rarely-changing first, code last

### Memory Limits

Starter = 512MB. Easy to OOM with Laravel + Vue build.

Watch in Render → Metrics. If you see OOM kills, upgrade plan.

Reduce memory:
- `OCTANE_MAX_REQUESTS=1000` (recycle workers)
- `pm.max_children=10` in php-fpm
- Don't run Telescope in production

### Cron Timing Drift

Render cron has a few-second jitter. Don't rely on exact timing.

Laravel scheduler handles this:
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('reports:daily')->dailyAt('03:00')->withoutOverlapping();
}
```

Then in Render, ONE cron service running every minute:
```yaml
- type: cron
  schedule: "* * * * *"
  dockerCommand: php artisan schedule:run
```

Laravel decides what to actually run.

### Region Choice

Pick region closest to:
1. Your users (latency)
2. Your DB (same region = internal hostname free)
3. Your S3 bucket (egress fees)

Once chosen, you can't move. Pick wisely.

### Cold Starts (Free Tier)

Free web services spin down after inactivity. 30-50s spin-up.
Paid services stay warm.

For production: always paid.

---

## Deploy Hooks

Trigger deploys from external:
```bash
curl -X POST https://api.render.com/deploy/srv-XXX?key=YYY
```

Use for:
- Sanity Studio publishing → deploy frontend
- Stripe price change → deploy
- Slack `/deploy` command

---

## Multi-Service Coordination

When web + worker + cron all depend on same code:

`render.yaml` references same Docker image. All deploy together. Same env vars.

But: ENV changes to one don't sync to others by default. Use `envVarGroups`:

```yaml
envVarGroups:
  - name: shared
    envVars:
      - key: APP_KEY
        generateValue: true
      - key: STRIPE_KEY
        sync: false

services:
  - name: web
    envVars:
      - fromGroup: shared
  - name: worker
    envVars:
      - fromGroup: shared
```

Change in one place. Applies everywhere.

---

## The Render Mental Model

Render is:
- ✅ Heroku, but with `render.yaml` IaC and lower price
- ✅ Vercel for backend
- ✅ Reliable Docker host with managed DBs
- ❌ Not Kubernetes (no custom networking/operators)
- ❌ Not AWS (no IAM roles, no SQS, etc.)
- ❌ Not Vercel-fast for static (use Cloudflare Pages for that)

Use it for: Laravel apps that need to ship and stay shipped.

---

## When Render Stops Being Right

You outgrow Render when:
- Bill exceeds $1000/mo (consider self-managed VPS)
- Need multi-region active-active
- Need custom networking (VPC peering, dedicated tenancy)
- Need GPUs / specialized hardware
- Have compliance (FedRAMP, HIPAA BAA — Render offers HIPAA on Pro)

Until then: Render is the answer.
