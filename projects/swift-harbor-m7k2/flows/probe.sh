#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=../lib/paths.sh
source "$SCRIPT_DIR/../lib/paths.sh"
# shellcheck source=../lib/browser.sh
source "$SCRIPT_DIR/../lib/browser.sh"

flow_probe() {
  require_agent_browser
  browser_open_session "probe"
  ab open "https://www.tiktok.com/signup"
  ab wait --load networkidle
  dismiss_cookies || true

  find_ref_click 'Use phone or email' || true
  ab wait 1000
  find_ref_click 'Sign up with email' || true
  ab wait 800

  if ! ab url 2>/dev/null | grep -q '/email'; then
    ab open "https://www.tiktok.com/signup/phone-or-email/email"
    ab wait --load networkidle
  fi

  ab_snapshot
  local url title
  url="$(current_url)"
  title="$(ab title 2>/dev/null | tail -1 || echo unknown)"

  local month email pass send otp
  month="$(grep -c 'combobox.*Month' "$SNAP_FILE" || true)"
  email="$(grep -c 'textbox.*Email' "$SNAP_FILE" || true)"
  pass="$(grep -c 'textbox.*Password' "$SNAP_FILE" || true)"
  send="$(grep -c 'button.*Send code' "$SNAP_FILE" || true)"
  otp="$(grep -c 'textbox.*6-digit' "$SNAP_FILE" || true)"

  cat <<EOF
{
  "ok": true,
  "url": "$url",
  "title": "$title",
  "month": $month,
  "email": $email,
  "password": $pass,
  "sendCode": $send,
  "otp": $otp
}
EOF
  screenshot_step probe
  browser_close
}

flow_probe "$@"
