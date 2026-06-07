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

> Note: if `docker-compose.override.yml` exists it is auto-loaded and turns the
> `web` service into a live-reload dev container (see below). It is gitignored;
> create it from the template to enable dev mode. To run the production-style
> image regardless, use `docker compose -f docker-compose.yml up`.

## Local development (live reload)

Enable dev mode once by creating the override from the template:

```powershell
copy docker-compose.override.yml.example docker-compose.override.yml
```

`docker compose up` then auto-loads `docker-compose.override.yml`, which:

- bind-mounts the source into `web` (edits apply with no rebuild),
- keeps the image's `vendor/`, `bootstrap/cache`, and `storage/framework` via
  anonymous volumes (the host has none / they must stay writable),
- layers `docker/php.dev.ini` so opcache revalidates changed files.

With `APP_ENV=local` the entrypoint clears config/route/view caches instead of
caching them, so edits are not frozen.

```powershell
docker compose up -d            # dev mode (override active)
# edit a .php file -> just refresh http://localhost:8080  (no rebuild)
```

Limits:

- **Frontend assets are not hot-reloaded.** Run `npm run dev` on the host for
  Vite HMR, or `npm run build` then refresh.
- **Only `web` is bind-mounted.** Editing Job/command/event classes used by the
  background roles needs `docker compose restart worker scheduler reverb`.
- First request after editing many files can be slightly slower (opcache
  revalidation). Fine for dev.

Run the production-style image locally (no override, baked code, caches on):

```powershell
docker compose -f docker-compose.yml up -d --build
```

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

| Service   | Role                | Host port | Health         |
|-----------|---------------------|-----------|----------------|
| migrate   | one-shot migrations | -         | exits 0        |
| web       | nginx + php-fpm     | 8080→8080 | HTTP `/up`     |
| reverb    | websocket server    | 8081→8080 | TCP :8080      |
| worker    | queue:work redis    | -         | none (no HTTP) |
| scheduler | schedule:work       | -         | none (no HTTP) |
| postgres  | postgres:16-alpine  | -         | pg_isready     |
| redis     | redis:7-alpine      | -         | redis-cli ping |
| backup    | scheduled pg_dump   | -         | none           |

Containers run as non-root (`www-data`); nginx listens on 8080 inside the
container. Roles switch off `CONTAINER_ROLE` env via `docker/entrypoint.sh`.

Migrations run once in the dedicated `migrate` service, which exits 0 on
success. `web`, `worker`, and `scheduler` wait for it via
`service_completed_successfully`, so scaling them to >1 replica cannot race
migrations. A failed migration leaves `migrate` with a non-zero exit and the
dependents never start (deploy fails loud instead of serving a broken schema).

On Coolify, this `migrate` service acts as the release/pre-deploy task — no
separate pre-deploy command is needed.

Backups: see [docs/backups.md](docs/backups.md). Security/key rotation: see
[docs/SECURITY-key-rotation.md](docs/SECURITY-key-rotation.md).

## Gotchas

- Start Docker Desktop before any `docker compose` command.
- Build may fail with `failed to resolve ... docker/dockerfile:1: TLS handshake
  timeout` — transient registry network issue. Just retry the build.
- Keep `.env` on the container values above. Resetting to sqlite breaks the
  postgres/redis services.
