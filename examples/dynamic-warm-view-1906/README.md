# Dynamic Warm View 1906

Laravel 13 API example: **health check**, **Sanctum auth**, and **user-owned tasks** — packaged for **ServerSideUp Docker** and **Render free tier** with **SQLite**.

## Local (Herd)

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd examples/dynamic-warm-view-1906
php artisan migrate --seed
php artisan test
```

Open **http://dynamic-warm-view-1906.test**

## API

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/healthz` | No | App + SQLite readiness (Render health check) |
| POST | `/api/register` | No | Register + token |
| POST | `/api/login` | No | Login + token |
| POST | `/api/logout` | Yes | Revoke current token |
| GET | `/api/user` | Yes | Current user |
| GET/POST | `/api/tasks` | Yes | List / create tasks |
| GET/PATCH/DELETE | `/api/tasks/{id}` | Yes | Show / update / delete own task |

Use header: `Authorization: Bearer {token}` and `Accept: application/json`.

Seeded user: `test@example.com` / `password`

## Docker (ServerSideUp)

```bash
# Ensure .env has APP_KEY (compose loads it via env_file)
export PATH="/d/laravel13.x/bin:$PATH"
php artisan key:generate --force

docker compose up --build
curl http://localhost:8089/api/healthz
```

If port bind fails, change `"8089:8080"` in `docker-compose.yml` to a free host port.

Image: [`serversideup/php:8.4-fpm-nginx`](https://hub.docker.com/r/serversideup/php)

## Render (free tier)

1. Push repo to GitHub
2. Create **Blueprint** from `render.yaml` in this directory
3. Set `APP_URL` to your Render URL after first deploy

SQLite on Render is **ephemeral** — data resets on redeploy unless you add persistent disk.

## Spec-Kit

Artifacts: `.specify/specs/001-dynamic_warm_view_1906/`
