#!/usr/bin/env bash
# Dev: Vite HMR. PHP is served by Herd when APP_URL ends with .test
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$ROOT/../.." && pwd)"
export PATH="$REPO_ROOT/bin:${PATH:-}"
PHP="${PHP:-/c/Users/vitou/.config/herd/bin/php84/php.exe}"
cd "$ROOT"

APP_URL="$(grep -m1 '^APP_URL=' .env 2>/dev/null | cut -d= -f2- | tr -d '\r' || true)"

if [[ "$APP_URL" == *".test"* ]]; then
  echo "→ Herd site: $APP_URL  (npm run dev = Vite only; no artisan serve)"
  npx vite
else
  echo "→ App URL: http://127.0.0.1:8000/  (not :5173)"
  npx concurrently -k \
    -n server,vite \
    -c "#93c5fd,#c4b5fd" \
    "\"$PHP\" artisan serve" \
    "npx vite"
fi
