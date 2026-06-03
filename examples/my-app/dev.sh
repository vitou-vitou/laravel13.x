#!/usr/bin/env bash
# Laravel (:8000) + Vite — calls vite directly (not npm run dev)
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$ROOT/../.." && pwd)"
export PATH="$REPO_ROOT/bin:${PATH:-}"
PHP="${PHP:-/c/Users/vitou/.config/herd/bin/php84/php.exe}"
cd "$ROOT"
echo "→ App URL: http://127.0.0.1:8000/  (not :5173)"
npx concurrently -k \
  -n server,vite \
  -c "#93c5fd,#c4b5fd" \
  "\"$PHP\" artisan serve" \
  "npx vite"
