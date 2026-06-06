#!/usr/bin/env bash
#
# sync-ngrok-oauth-env.sh
#
# Point Google / Microsoft / GitHub OAuth redirect URIs at the static ngrok dev
# domain so provider consoles only need to be configured once.
#
# Usage:
#   ./scripts/sync-ngrok-oauth-env.sh
#   ./scripts/sync-ngrok-oauth-env.sh --domain your-name.ngrok-free.dev
#
# Requires: NGROK_DEV_DOMAIN in .env (or pass --domain once to set it).

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${SCRIPT_DIR}/.."
ENV_FILE="${APP_DIR}/.env"
PHP="${PHP:-/d/laravel13.x/bin/php}"

DOMAIN=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --domain)
      DOMAIN="${2:-}"
      shift 2
      ;;
    -h|--help)
      sed -n '2,12p' "$0" | sed 's/^# \{0,1\}//'
      exit 0
      ;;
    *)
      echo "Unknown argument: $1" >&2
      exit 1
      ;;
  esac
done

if [[ ! -f "${ENV_FILE}" ]]; then
  echo "Error: .env not found at ${ENV_FILE}" >&2
  exit 1
fi

read_env() {
  local key="$1"
  grep -E "^${key}=" "${ENV_FILE}" | head -n1 | cut -d'=' -f2- | tr -d '"'"'"' \r' || true
}

normalize_ngrok_domain() {
  local raw="$1"
  raw="${raw#https://}"
  raw="${raw#http://}"
  raw="${raw%%/*}"
  raw="${raw%/}"
  echo "${raw}"
}

validate_ngrok_domain() {
  local host="$1"

  if [[ -z "${host}" ]]; then
    echo "Error: domain is empty." >&2
    exit 1
  fi

  if [[ "${host}" == *"://"* ]]; then
    echo "Error: pass hostname only, not a full URL." >&2
    echo "  Wrong:  --domain http://dashboard-v1.test" >&2
    echo "  Right:  --domain abc123.ngrok-free.dev" >&2
    exit 1
  fi

  if [[ "${host}" == *.test || "${host}" == localhost* ]]; then
    cat >&2 <<EOF
Error: "${host}" is a local Herd URL, not an ngrok public domain.

Google/GitHub/Microsoft OAuth need a public HTTPS ngrok host.
Keep APP_URL=http://dashboard-v1.test for local browsing.
Set NGROK_DEV_DOMAIN to your ngrok dev domain from:
  https://dashboard.ngrok.com/domains
EOF
    exit 1
  fi

  if [[ ! "${host}" =~ \.ngrok(-free)?\.(app|dev|pizza)$ ]]; then
    echo "Error: \"${host}\" does not look like an ngrok domain." >&2
    echo "Expected something like: abc123.ngrok-free.dev" >&2
    exit 1
  fi
}

if [[ -n "${DOMAIN}" ]]; then
  DOMAIN="$(normalize_ngrok_domain "${DOMAIN}")"
  validate_ngrok_domain "${DOMAIN}"
  if grep -qE '^NGROK_DEV_DOMAIN=' "${ENV_FILE}"; then
    sed -i "s|^NGROK_DEV_DOMAIN=.*|NGROK_DEV_DOMAIN=${DOMAIN}|" "${ENV_FILE}"
  else
    printf '\n# ngrok static dev domain — https://dashboard.ngrok.com/domains\nNGROK_DEV_DOMAIN=%s\n' "${DOMAIN}" >> "${ENV_FILE}"
  fi
  echo "Set NGROK_DEV_DOMAIN=${DOMAIN}"
fi

DOMAIN="$(normalize_ngrok_domain "$(read_env NGROK_DEV_DOMAIN)")"

if [[ -z "${DOMAIN}" ]]; then
  cat >&2 <<'EOF'
Error: NGROK_DEV_DOMAIN is not set.

1. Open https://dashboard.ngrok.com/domains and copy your assigned dev domain
   (e.g. abc123xyz.ngrok-free.dev — free accounts get one permanent domain).
2. Run:
     ./scripts/sync-ngrok-oauth-env.sh --domain YOUR-DOMAIN.ngrok-free.dev
EOF
  exit 1
fi

validate_ngrok_domain "${DOMAIN}"

BASE="https://${DOMAIN}"

set_redirect() {
  local key="$1"
  local path="$2"
  local value="${BASE}${path}"

  if grep -qE "^${key}=" "${ENV_FILE}"; then
    sed -i "s|^${key}=.*|${key}=${value}|" "${ENV_FILE}"
  else
    echo "${key}=${value}" >> "${ENV_FILE}"
  fi
  echo "  ${key}=${value}"
}

echo "Syncing OAuth redirect URIs to ${BASE} ..."
set_redirect GOOGLE_REDIRECT_URI /auth/google/callback
set_redirect MICROSOFT_REDIRECT_URI /auth/microsoft/callback
set_redirect GITHUB_REDIRECT_URI /auth/github/callback

if [[ -x "${PHP}" ]] || command -v "${PHP}" >/dev/null 2>&1; then
  ( cd "${APP_DIR}" && "${PHP}" artisan config:clear )
  echo "Ran php artisan config:clear"
fi

cat <<EOF

Provider consoles (configure once with this static URL):

Google Cloud Console → APIs & Credentials → your Web client:
  Authorized JavaScript origins: ${BASE}
  Authorized redirect URIs:        ${BASE}/auth/google/callback
  (Remove old ngrok URLs like 9cbb-…ngrok-free.app or 8262-…)

Microsoft Entra → App registration → Authentication:
  Redirect URI: ${BASE}/auth/microsoft/callback

GitHub → OAuth App settings:
  Homepage URL: ${BASE}
  Callback URL: ${BASE}/auth/github/callback

Open SSO login at: ${BASE}/login
Run: ./scripts/print-google-oauth-console.sh
EOF
