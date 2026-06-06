#!/usr/bin/env bash
#
# ngrok-vitou-dev-http.sh
#
# Expose dashboard-v1 on Herd through ngrok using your *static* dev domain so
# GitHub / Google / Microsoft OAuth callbacks stay valid across restarts.
#
# Usage:
#   ./scripts/ngrok-vitou-dev-http.sh
#
# One-time setup:
#   1. Claim your dev domain: https://dashboard.ngrok.com/domains
#   2. ./scripts/sync-ngrok-oauth-env.sh --domain YOUR-DOMAIN.ngrok-free.dev
#   3. Update OAuth provider consoles with the printed URLs (once)
#
# Requires: ngrok (authed), NGROK_DEV_DOMAIN in .env, ngrok-traffic-policy.yml

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${SCRIPT_DIR}/.."
ENV_FILE="${APP_DIR}/.env"
POLICY_FILE="${APP_DIR}/ngrok-traffic-policy.yml"

if [[ ! -f "${ENV_FILE}" ]]; then
  echo "Error: .env not found at ${ENV_FILE}" >&2
  exit 1
fi

if [[ ! -f "${POLICY_FILE}" ]]; then
  echo "Error: traffic policy not found at ${POLICY_FILE}" >&2
  exit 1
fi

read_env() {
  local key="$1"
  grep -E "^${key}=" "${ENV_FILE}" | head -n1 | cut -d'=' -f2- | tr -d '"'"'"' \r' || true
}

NGROK_DEV_DOMAIN="$(read_env NGROK_DEV_DOMAIN)"

if [[ -z "${NGROK_DEV_DOMAIN}" ]]; then
  cat >&2 <<'EOF'
Error: NGROK_DEV_DOMAIN is not set in .env.

Dynamic ngrok URLs break OAuth (GitHub, Google, Microsoft) on every restart.

Fix (one-time):
  1. https://dashboard.ngrok.com/domains — copy your assigned dev domain
  2. ./scripts/sync-ngrok-oauth-env.sh --domain YOUR-DOMAIN.ngrok-free.dev
  3. Update provider consoles with the printed callback URLs
EOF
  exit 1
fi

PUBLIC_URL="https://${NGROK_DEV_DOMAIN}"

# ngrok + Vite dev (:5173) breaks SSO: mixed content, unreachable HMR, manifest errors.
if pgrep -f "[v]ite" >/dev/null 2>&1 || pgrep -f "npm run dev" >/dev/null 2>&1; then
  cat >&2 <<'EOF'
Error: Vite dev server is running (npm run dev).

Stop it before ngrok SSO — otherwise public/hot points at :5173 and login/dashboard fail
through the HTTPS tunnel (Vite manifest / mixed-content errors).

  Ctrl+C in the Vite terminal, then re-run:
    ./scripts/ngrok-vitou-dev-http.sh
EOF
  exit 1
fi

echo "Building assets for ngrok (production bundle, not Vite dev)..."
( cd "${APP_DIR}" && npm run build )

HOT_RESTORED=0
if [[ -f "${APP_DIR}/public/hot" ]]; then
  echo "Disabling Vite dev mode (public/hot -> public/hot.disabled) for tunnel."
  mv "${APP_DIR}/public/hot" "${APP_DIR}/public/hot.disabled"
  HOT_RESTORED=1
fi

cleanup() {
  if [[ "${HOT_RESTORED}" -eq 1 && -f "${APP_DIR}/public/hot.disabled" ]]; then
    mv "${APP_DIR}/public/hot.disabled" "${APP_DIR}/public/hot"
    echo "Restored Vite dev mode (public/hot)."
  fi
}
trap cleanup EXIT INT TERM

# Warn if redirect URIs still point at a different host (common after dynamic tunnels).
for key in GOOGLE_REDIRECT_URI MICROSOFT_REDIRECT_URI GITHUB_REDIRECT_URI; do
  uri="$(read_env "${key}")"
  if [[ -n "${uri}" && "${uri}" != "${PUBLIC_URL}"* ]]; then
    echo "Warning: ${key} is ${uri}"
    echo "         Run ./scripts/sync-ngrok-oauth-env.sh to align with ${PUBLIC_URL}"
  fi
done

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

# Probe the public URL; 200 = Herd/Laravel, 400 ERR_NGROK_3801 = stale pooled endpoint elsewhere.
tunnel_probe_ok() {
  local code
  code="$(curl -s -o /dev/null -w '%{http_code}' \
    -H 'ngrok-skip-browser-warning: true' \
    --max-time 5 \
    "${PUBLIC_URL}/login" 2>/dev/null || echo '000')"
  [[ "${code}" == "200" ]]
}

echo "Static ngrok URL: ${PUBLIC_URL}"
echo "Forwarding 127.0.0.1:80 -> Herd (Host: dashboard-v1.test via traffic policy)"
echo "SSO login: ${PUBLIC_URL}/login"
echo ""
echo "Note: do NOT use --pooling-enabled — a second endpoint on this domain causes"
echo "      intermittent ERR_NGROK_3801 (refresh fixes ~50% of loads). Stop other agents:"
echo "      https://dashboard.ngrok.com/endpoints"
echo ""

API="http://127.0.0.1:4040/api/tunnels"

(
  for _ in $(seq 1 45); do
    sleep 1
    public_url="$(curl -s "${API}" 2>/dev/null | grep -oE '"public_url":"https://[^"]+"' | head -n1 | cut -d'"' -f4)"
    if [[ -n "${public_url}" ]]; then
      if [[ "${public_url}" != "${PUBLIC_URL}" ]]; then
        echo "Warning: tunnel URL is ${public_url}, expected ${PUBLIC_URL}" >&2
      fi
      ok_streak=0
      for _ in $(seq 1 20); do
        if tunnel_probe_ok; then
          ok_streak=$((ok_streak + 1))
          if [[ "${ok_streak}" -ge 3 ]]; then
            open_url "${public_url}/login"
            exit 0
          fi
        else
          ok_streak=0
        fi
        sleep 1
      done
      echo "Warning: tunnel is up but ${PUBLIC_URL}/login still returns errors." >&2
      echo "         Another ngrok agent may be pooled on this domain — stop it at:" >&2
      echo "         https://dashboard.ngrok.com/endpoints" >&2
      open_url "${public_url}/login"
      exit 0
    fi
  done
  echo "Timed out waiting for ngrok tunnel; check ${API}" >&2
) &

exec ngrok http 127.0.0.1:80 \
  --url "${PUBLIC_URL}" \
  --traffic-policy-file "${POLICY_FILE}"
