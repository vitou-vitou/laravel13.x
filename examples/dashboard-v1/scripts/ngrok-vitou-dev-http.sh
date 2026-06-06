#!/usr/bin/env bash
#
# ngrok-vitou-dev-http.sh
#
# Expose the local dashboard-v1 app (.test vhost) through ngrok.
#
# Fixes the "404 Not Found" problem: ngrok forwards the public
# *.ngrok-free.app hostname as the HTTP Host header. The local web
# server (Laragon/Valet/nginx/apache) routes by Host header, finds no
# vhost matching the ngrok domain, and returns 404. --host-header=rewrite
# rewrites the Host to the local .test domain so the vhost matches.
#
# Usage:
#   ./scripts/ngrok-vitou-dev-http.sh
#
# Requires: ngrok (authed), bash, the .env file with APP_URL set.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SCRIPT_DIR}/../.env"

if [[ ! -f "${ENV_FILE}" ]]; then
  echo "Error: .env not found at ${ENV_FILE}" >&2
  exit 1
fi

# Read APP_URL from .env (strip quotes and surrounding whitespace).
APP_URL="$(grep -E '^APP_URL=' "${ENV_FILE}" | head -n1 | cut -d'=' -f2- | tr -d '"'"'"' \r')"

if [[ -z "${APP_URL}" ]]; then
  echo "Error: APP_URL not set in ${ENV_FILE}" >&2
  exit 1
fi

# Strip scheme -> host[:port]. Default port 80 if none present.
HOST_PORT="${APP_URL#http://}"
HOST_PORT="${HOST_PORT#https://}"
HOST_PORT="${HOST_PORT%/}"
if [[ "${HOST_PORT}" != *:* ]]; then
  HOST_PORT="${HOST_PORT}:80"
fi

HOST="${HOST_PORT%%:*}"

APP_DIR="${SCRIPT_DIR}/.."

# Over an https ngrok tunnel, the Vite dev server (public/hot -> http://[::1]:5173)
# causes mixed-content blocks and unreachable HMR, so the browser hangs "loading".
# Force built assets: ensure a build exists, then move the hot file aside.
if [[ ! -f "${APP_DIR}/public/build/manifest.json" ]]; then
  echo "No Vite build found; running 'npm run build'..."
  ( cd "${APP_DIR}" && npm run build )
fi

HOT_RESTORED=0
if [[ -f "${APP_DIR}/public/hot" ]]; then
  echo "Disabling Vite dev mode (public/hot -> public/hot.disabled) for tunnel."
  mv "${APP_DIR}/public/hot" "${APP_DIR}/public/hot.disabled"
  HOT_RESTORED=1
fi

# Restore dev mode on exit so local `npm run dev` keeps working.
cleanup() {
  if [[ "${HOT_RESTORED}" -eq 1 && -f "${APP_DIR}/public/hot.disabled" ]]; then
    mv "${APP_DIR}/public/hot.disabled" "${APP_DIR}/public/hot"
    echo "Restored Vite dev mode (public/hot)."
  fi
}
trap cleanup EXIT INT TERM

echo "Forwarding ngrok -> ${HOST_PORT} (Host header rewritten to ${HOST})"

# ngrok web-inspect API (used to discover the public URL once the tunnel is up).
API="http://127.0.0.1:4040/api/tunnels"

# Open a URL in the default browser, cross-platform.
open_url() {
  local url="$1"
  if command -v powershell.exe >/dev/null 2>&1; then
    powershell.exe -NoProfile -Command "Start-Process '${url}'" >/dev/null 2>&1
  elif command -v cmd.exe >/dev/null 2>&1; then
    cmd.exe /c start "" "${url}" >/dev/null 2>&1
  elif command -v xdg-open >/dev/null 2>&1; then
    xdg-open "${url}" >/dev/null 2>&1
  elif command -v open >/dev/null 2>&1; then
    open "${url}" >/dev/null 2>&1
  else
    echo "Open manually: ${url}"
  fi
}

# Poll the API in the background, then open the public URL in the browser.
(
  for _ in $(seq 1 30); do
    sleep 1
    public_url="$(curl -s "${API}" | grep -oE '"public_url":"https://[^"]+"' | head -n1 | cut -d'"' -f4)"
    if [[ -n "${public_url}" ]]; then
      echo "Public URL: ${public_url}"
      open_url "${public_url}"
      exit 0
    fi
  done
  echo "Timed out waiting for ngrok tunnel; check ${API}" >&2
) &

ngrok http --host-header=rewrite "${HOST_PORT}"
