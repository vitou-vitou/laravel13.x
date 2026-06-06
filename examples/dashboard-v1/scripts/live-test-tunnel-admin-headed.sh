#!/usr/bin/env bash
#
# live-test-tunnel-admin-headed.sh
#
# Watchable E2E of Filament tunnel admin (real browser window).
# Requires: agent-browser, Herd (dashboard-v1.test), optional ngrok for Verify.
#
# Usage:
#   ./scripts/live-test-tunnel-admin-headed.sh
#   ./scripts/live-test-tunnel-admin-headed.sh --record   # also saves demo.webm
#
# Tips: refs (@eN) change after every navigation — script re-snapshots each step.

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${SCRIPT_DIR}/.."
cd "${APP_DIR}"

RECORD=0
if [[ "${1:-}" == "--record" ]]; then
  RECORD=1
fi

read_env() {
  local key="$1"
  grep -E "^${key}=" .env 2>/dev/null | head -n1 | cut -d'=' -f2- | tr -d '"'"'"' \r' || true
}

DOMAIN="$(read_env NGROK_DEV_DOMAIN)"
if [[ -z "${DOMAIN}" ]]; then
  DOMAIN="your-name.ngrok-free.dev"
  echo "Warning: NGROK_DEV_DOMAIN not set — using placeholder ${DOMAIN}" >&2
fi

TS="$(date +%s)"
TUNNEL_NAME="Headed demo ${TS}"
VIDEO="${APP_DIR}/storage/app/tunnel-headed-demo-${TS}.webm"
SHOT="${APP_DIR}/storage/app/tunnel-headed-demo-${TS}.png"

# Visible browser for every command in this session
AB=(agent-browser --headed)

pause() {
  "${AB[@]}" wait "${1:-1200}" >/dev/null
}

snap() {
  "${AB[@]}" snapshot -i -c > "${TMPDIR:-/tmp}/ab-headed-$$.txt"
}

ref() {
  grep -F "$1" "${TMPDIR:-/tmp}/ab-headed-$$.txt" | head -n1 | sed -n 's/.*ref=\(e[0-9]*\).*/\1/p'
}

echo "Opening visible Chrome — watch the window."
"${AB[@]}" close --all 2>/dev/null || true

if [[ "${RECORD}" -eq 1 ]]; then
  echo "Recording → ${VIDEO}"
  "${AB[@]}" record start "${VIDEO}"
fi

echo "→ Admin login"
"${AB[@]}" open "http://dashboard-v1.test/admin/login"
"${AB[@]}" wait --load networkidle
pause 1500
snap
E="$(ref 'textbox "Email')"
P="$(ref 'textbox "Password')"
S="$(ref 'button "Sign in"')"

"${AB[@]}" click "@${E}"
pause 400
"${AB[@]}" type "@${E}" "test@example.com"
pause 800
"${AB[@]}" click "@${P}"
pause 400
"${AB[@]}" type "@${P}" "password"
pause 800
"${AB[@]}" click "@${S}"
"${AB[@]}" wait --load networkidle
pause 1500

echo "→ Create tunnel"
"${AB[@]}" open "http://dashboard-v1.test/admin/tunnels/create"
"${AB[@]}" wait --load networkidle
pause 1500
snap
N="$(ref 'textbox "Name')"
D="$(ref 'textbox "Domain')"
H="$(ref 'textbox "Herd host')"
C="$(ref 'button "Create"')"

"${AB[@]}" click "@${N}"
pause 300
"${AB[@]}" fill "@${N}" ""
"${AB[@]}" type "@${N}" "${TUNNEL_NAME}"
pause 600
"${AB[@]}" click "@${D}"
pause 300
"${AB[@]}" fill "@${D}" ""
"${AB[@]}" type "@${D}" "${DOMAIN}"
pause 600
"${AB[@]}" click "@${H}"
pause 300
"${AB[@]}" fill "@${H}" "dashboard-v1.test"
pause 800
"${AB[@]}" click "@${C}"
"${AB[@]}" wait --load networkidle
pause 2000

echo "→ Activate"
snap
A="$(ref 'button "Activate"')"
if [[ -n "${A}" ]]; then
  "${AB[@]}" click "@${A}"
  pause 1200
  snap
  F="$(ref 'button "Confirm"')"
  "${AB[@]}" click "@${F}"
  "${AB[@]}" wait --load networkidle
  pause 2000
fi

echo "→ Verify health (needs ngrok up for green result)"
snap
V="$(ref 'Verify health')"
if [[ -n "${V}" ]]; then
  "${AB[@]}" click "@${V}"
  pause 4000
fi

"${AB[@]}" screenshot "${SHOT}"
echo "Screenshot: ${SHOT}"

if [[ "${RECORD}" -eq 1 ]]; then
  "${AB[@]}" record stop
  echo "Video: ${VIDEO}"
fi

echo ""
echo "Done. Browser stays open 15s so you can look around — close manually or wait."
pause 15000
"${AB[@]}" close --all
