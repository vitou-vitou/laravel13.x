# Running dashboard-v1 with Docker

Full stack via Docker Compose: web (nginx + php-fpm), reverb (websockets),
worker (queue), scheduler, postgres, redis.

## Prerequisites

- Docker Desktop running.
- `.env` present and configured for the containers (NOT the sqlite defaults):
  - `DB_CONNECTION=pgsql`, `DB_HOST=postgres`, `DB_PORT=5432`
  - `DB_DATABASE=dashboard`, `DB_USERNAME=dashboard`, `DB_PASSWORD=secret`
  - `REDIS_HOST=redis`, `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `SESSION_DRIVER=redis`
  - `APP_KEY=base64:...` (required — entrypoint exits without it)

## Normal start

```powershell
cd D:\laravel13.x\examples\dashboard-v1
docker compose up -d
```

Uses the existing `dashboard-v1` image. Database/redis data persists in named
volumes (`pgdata`, `redisdata`), so it survives restarts.

## Check health

```powershell
docker compose ps
```

Wait for `web` and `reverb` to show `(healthy)`. Then:

- App:    http://localhost:8080
- Reverb: ws://localhost:8081

Quick endpoint check:

```powershell
curl http://localhost:8080/up
```

## Stop

```powershell
docker compose down        # stop, keep data
docker compose down -v      # stop and WIPE postgres + redis data
```

## Rebuild

Only needed after changing code, `Dockerfile`, `composer.json`/`.lock`, or
`package.json`/`.lock`:

```powershell
docker compose build
docker compose up -d
```

## Common commands

```powershell
docker compose logs -f web       # tail web logs
docker compose restart web       # restart one service
docker compose exec web sh       # shell into web container
docker compose exec web php artisan migrate   # run artisan manually
```

## Services & ports

| Service   | Role               | Host port | Health |
|-----------|--------------------|-----------|--------|
| web       | nginx + php-fpm     | 8080→80   | HTTP `/up` |
| reverb    | websocket server    | 8081→8080 | TCP :8080 |
| worker    | queue:work redis    | -         | none (no HTTP) |
| scheduler | schedule:work       | -         | none (no HTTP) |
| postgres  | postgres:16-alpine  | -         | pg_isready |
| redis     | redis:7-alpine      | -         | redis-cli ping |

Roles switch off `CONTAINER_ROLE` env via `docker/entrypoint.sh`. The `web`
role runs migrations on boot; a failed migration aborts the boot (won't serve
against a broken schema).

## Gotchas

- Start Docker Desktop before any `docker compose` command.
- Build may fail with `failed to resolve ... docker/dockerfile:1: TLS handshake
  timeout` — transient registry network issue. Just retry the build.
- Keep `.env` on the container values above. Resetting to sqlite breaks the
  postgres/redis services.
