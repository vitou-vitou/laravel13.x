#!/usr/bin/env bash
# Print exact Google Cloud Console values for the current NGROK_DEV_DOMAIN.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="${SCRIPT_DIR}/../.env"

read_env() {
  local key="$1"
  grep -E "^${key}=" "${ENV_FILE}" | head -n1 | cut -d'=' -f2- | tr -d '"'"'"' \r' || true
}

DOMAIN="$(read_env NGROK_DEV_DOMAIN)"
CLIENT_ID="$(read_env GOOGLE_CLIENT_ID)"

if [[ -z "${DOMAIN}" ]]; then
  echo "NGROK_DEV_DOMAIN not set. Run sync-ngrok-oauth-env.sh first." >&2
  exit 1
fi

BASE="https://${DOMAIN}"

cat <<EOF
Google Cloud Console — OAuth 2.0 Client (Web application)
Client ID: ${CLIENT_ID}

Open: https://console.cloud.google.com/apis/credentials

Edit your Web client → set EXACTLY (no trailing slashes on origin):

  Authorized JavaScript origins:
    ${BASE}

  Authorized redirect URIs:
    ${BASE}/auth/google/callback

Save, wait ~1 minute, then retry:
  ${BASE}/login

Laravel redirect_uri sent to Google:
  ${BASE}/auth/google/callback
EOF

if command -v powershell.exe >/dev/null 2>&1; then
  powershell.exe -NoProfile -Command "Start-Process 'https://console.cloud.google.com/apis/credentials'" >/dev/null 2>&1 || true
fi
