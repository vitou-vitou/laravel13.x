#!/usr/bin/env bash
# Probe static ngrok domain — login HTML + built CSS must return 200 every time.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SCRIPT_DIR}/../.env"

read_env() {
  local key="$1"
  grep -E "^${key}=" "${ENV_FILE}" | head -n1 | cut -d'=' -f2- | tr -d '"'"'"' \r' || true
}

DOMAIN="$(read_env NGROK_DEV_DOMAIN)"
TRIES="${1:-10}"

if [[ -z "${DOMAIN}" ]]; then
  echo "NGROK_DEV_DOMAIN not set in .env" >&2
  exit 1
fi

BASE="https://${DOMAIN}"
HEADER=(-H "ngrok-skip-browser-warning: true")

fail=0
css_path=""

echo "Probing ${BASE} (${TRIES} attempts)..."

for i in $(seq 1 "${TRIES}"); do
  login_code="$(curl -s -o /tmp/ngrok-login.html -w '%{http_code}' "${HEADER[@]}" "${BASE}/login" || echo 000)"
  if [[ "${login_code}" != "200" ]]; then
    echo "  ${i}: login HTTP ${login_code} FAIL"
    fail=$((fail + 1))
    continue
  fi

  if [[ -z "${css_path}" ]]; then
    css_path="$(grep -oE '/build/assets/[^"]+\.css' /tmp/ngrok-login.html | head -n1 || true)"
  fi

  if [[ -z "${css_path}" ]]; then
    echo "  ${i}: login OK but no Vite CSS in HTML (is public/hot enabled? run npm run build)" >&2
    exit 1
  fi

  css_code="$(curl -s -o /dev/null -w '%{http_code}' "${HEADER[@]}" "${BASE}${css_path}" || echo 000)"
  if [[ "${css_code}" == "200" ]]; then
    echo "  ${i}: login + CSS OK"
  else
    echo "  ${i}: login OK, CSS HTTP ${css_code} FAIL"
    fail=$((fail + 1))
  fi
done

if [[ "${fail}" -gt 0 ]]; then
  cat >&2 <<EOF

${fail}/${TRIES} probes failed — broken styles / ERR_NGROK_3801 on refresh.

Fix:
  1. https://dashboard.ngrok.com/endpoints — stop ALL endpoints on ${DOMAIN}
  2. Stop other ngrok terminals / cloud agents
  3. ./scripts/ngrok-vitou-dev-http.sh  (no --pooling-enabled)
  4. Re-run: ./scripts/verify-ngrok-tunnel.sh
EOF
  exit 1
fi

echo "OK: tunnel healthy (${TRIES}/${TRIES})."
