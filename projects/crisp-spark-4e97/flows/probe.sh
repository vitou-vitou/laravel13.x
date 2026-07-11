#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=../lib/paths.sh
source "$SCRIPT_DIR/../lib/paths.sh"
# shellcheck source=../lib/settings.sh
source "$SCRIPT_DIR/../lib/settings.sh"
# shellcheck source=../lib/ldplayer.sh
source "$SCRIPT_DIR/../lib/ldplayer.sh"
# shellcheck source=../lib/adb.sh
source "$SCRIPT_DIR/../lib/adb.sh"
# shellcheck source=../lib/ui.sh
source "$SCRIPT_DIR/../lib/ui.sh"

flow_probe() {
  load_settings
  adb_wait_ready 8 || { echo '{"ok":false,"error":"adb"}'; return 1; }

  ld_kill_app
  sleep 1
  ld_run_app
  sleep 6
  ui_dismiss_popups

  ui_dump || true
  local has_profile has_signup has_login
  has_profile=0; has_signup=0; has_login=0
  ui_has_text "Profile" && has_profile=1 || true
  ui_has_text "Sign up" && has_signup=1 || true
  ui_has_text "Log in" && has_login=1 || true

  screenshot_step probe
  cat <<EOF
{
  "ok": true,
  "package": "$SETTINGS_TIKTOK_PKG",
  "profileTab": $has_profile,
  "signUp": $has_signup,
  "logIn": $has_login,
  "adb": true
}
EOF
}

flow_probe "$@"
