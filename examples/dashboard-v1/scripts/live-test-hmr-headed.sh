#!/usr/bin/env bash
#
# live-test-hmr-headed.sh
#
# Visible browser demo: appends a temporary CSS rule, Vite HMR should apply
# a magenta outline without refresh. Restores app.css on exit.
#
# Prerequisite:
#   npm run dev   (in another terminal)
#   ./scripts/verify-vite-dev.sh   (optional sanity check)
#
# Usage:
#   ./scripts/live-test-hmr-headed.sh

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${SCRIPT_DIR}/.."
CSS_FILE="${APP_DIR}/resources/css/app.css"
ENV_FILE="${APP_DIR}/.env"
MARKER="hmr-headed-test"

read_env() {
  local key="$1"
  grep -E "^${key}=" "${ENV_FILE}" 2>/dev/null | head -n1 | cut -d'=' -f2- | tr -d '"'"'"' \r' || true
}

APP_URL="$(read_env APP_URL)"
APP_URL="${APP_URL:-http://dashboard-v1.test}"
TARGET="${APP_URL}/login"

AB=(agent-browser --headed)

restore_css() {
  if [[ -f "${CSS_FILE}.${MARKER}.bak" ]]; then
    mv "${CSS_FILE}.${MARKER}.bak" "${CSS_FILE}"
    echo "Restored ${CSS_FILE}"
  fi
}

cleanup() {
  restore_css
  "${AB[@]}" close --all 2>/dev/null || true
}
trap cleanup EXIT INT TERM

if [[ ! -f "${APP_DIR}/public/hot" ]]; then
  echo "Error: public/hot missing. Run: npm run dev" >&2
  exit 1
fi

if grep -q "${MARKER}" "${CSS_FILE}" 2>/dev/null; then
  echo "Error: HMR test marker already in app.css — restore or remove first." >&2
  exit 1
fi

echo "→ Checking Vite dev smoke..."
"${SCRIPT_DIR}/verify-vite-dev.sh"

echo ""
echo "→ Opening visible Chrome at ${TARGET}"
"${AB[@]}" close --all 2>/dev/null || true
"${AB[@]}" open "${TARGET}"
"${AB[@]}" wait --load networkidle
"${AB[@]}" wait 1500

echo "→ Appending temporary outline rule to resources/css/app.css"
cp "${CSS_FILE}" "${CSS_FILE}.${MARKER}.bak"
cat >> "${CSS_FILE}" <<'EOF'

/* hmr-headed-test — auto-removed on exit */
body { outline: 5px solid #e879f9 !important; outline-offset: -5px; }
EOF

echo ""
echo "Watch the browser — a MAGENTA outline should appear within ~3s (no manual refresh)."
echo "If nothing changes, Vite HMR is not connected."
"${AB[@]}" wait 4000

TS="$(date +%s)"
SHOT="${APP_DIR}/storage/app/hmr-headed-${TS}.png"
"${AB[@]}" screenshot "${SHOT}"
echo "Screenshot: ${SHOT}"

echo ""
echo "Leaving browser open 20s — edit app.css yourself to see further HMR updates."
"${AB[@]}" wait 20000

echo "Done."
