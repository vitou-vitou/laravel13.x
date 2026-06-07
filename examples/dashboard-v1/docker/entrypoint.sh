#!/usr/bin/env sh
set -e

ROLE="${CONTAINER_ROLE:-web}"

echo "[entrypoint] starting role=${ROLE}"

# Fail fast if the app key is missing — config:cache + encryption need it.
if [ -z "${APP_KEY}" ]; then
  echo "[entrypoint] FATAL: APP_KEY is not set. Set it in the deploy env."
  exit 1
fi

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

# Storage link is idempotent and web-local (safe per replica).
# Migrations are NOT run here anymore — they run once in the dedicated `migrate`
# role (Risk #5), so scaling web to >1 replica can't race migrations.
if [ "$ROLE" = "web" ]; then
  php artisan storage:link || true
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
  migrate)
    # One-shot release task: run migrations and exit. set -e aborts on failure
    # so the deploy fails loud instead of serving a broken schema.
    php artisan migrate --force
    echo "[entrypoint] migrations complete"
    exit 0
    ;;
  *)
    echo "[entrypoint] unknown role: $ROLE"
    exit 1
    ;;
esac
