#!/usr/bin/env bash
#
# verify-vite-dev.sh
#
# Smoke-check that Vite dev (HMR) is running for local Herd dev.
# Use on http://dashboard-v1.test — NOT with ngrok (tunnel uses npm run build).
#
# Usage:
#   npm run dev    # terminal 1
#   ./scripts/verify-vite-dev.sh

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${SCRIPT_DIR}/.."
HOT_FILE="${APP_DIR}/public/hot"
ENV_FILE="${APP_DIR}/.env"

read_env() {
  local key="$1"
  grep -E "^${key}=" "${ENV_FILE}" 2>/dev/null | head -n1 | cut -d'=' -f2- | tr -d '"'"'"' \r' || true
}

fail() {
  echo "FAIL: $*" >&2
  exit 1
}

APP_URL="$(read_env APP_URL)"
APP_URL="${APP_URL:-http://dashboard-v1.test}"

if [[ ! -f "${HOT_FILE}" ]]; then
  if [[ -f "${APP_DIR}/public/hot.disabled" ]]; then
    fail "public/hot is disabled (tunnel mode). Stop ngrok script, run: npm run dev"
  fi
  fail "public/hot missing — start Vite: npm run dev (or ./dev.sh)"
fi

VITE_ORIGIN="$(tr -d '\r\n' < "${HOT_FILE}")"
VITE_ORIGIN="${VITE_ORIGIN%/}"

if [[ -z "${VITE_ORIGIN}" ]]; then
  fail "public/hot is empty"
fi

echo "Vite origin: ${VITE_ORIGIN}"
echo "App URL:     ${APP_URL}"

client_code="$(curl -s -o /dev/null -w '%{http_code}' "${VITE_ORIGIN}/@vite/client" 2>/dev/null || echo 000)"
if [[ "${client_code}" != "200" ]]; then
  fail "Vite client HTTP ${client_code} — is npm run dev running?"
fi
echo "OK: @vite/client → ${client_code}"

login_code="$(curl -s -o /tmp/vite-dev-login.html -w '%{http_code}' "${APP_URL}/login" 2>/dev/null || echo 000)"
if [[ "${login_code}" != "200" ]]; then
  fail "Login page HTTP ${login_code} — is Herd up for ${APP_URL}?"
fi
echo "OK: login page → ${login_code}"

if grep -qE 'vite/client|@vite/client|:5173|\[::1\]:5173' /tmp/vite-dev-login.html; then
  echo "OK: login HTML references Vite dev server"
else
  fail "login HTML has no Vite dev markers — wrong APP_URL or cached config?"
fi

echo ""
echo "HMR ready. Open ${APP_URL}/login (not :5173)."
echo "Watchable demo: ./scripts/live-test-hmr-headed.sh"
