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
# shellcheck source=../lib/accounts.sh
source "$SCRIPT_DIR/../lib/accounts.sh"

EMAIL="${EMAIL:-}"
PASSWORD="${PASSWORD:-}"

flow_login() {
  load_settings
  adb_wait_ready 8 || { echo "login: adb_unavailable"; return 1; }

  local email password
  email="${EMAIL:-$(accounts_latest_email)}"
  password="${PASSWORD:-$(accounts_latest_password)}"
  if [[ -z "$email" || -z "$password" ]]; then
    echo "login: no_account" >&2
    return 2
  fi

  echo "==> login $email"
  ld_kill_app
  sleep 1
  ld_run_app
  sleep 8
  ui_dismiss_popups

  ui_tap_any "Profile" "Me" || adb_tap 1450 850
  sleep 3

  if ui_has_text "Log in" || ui_has_text "Login"; then
    ui_tap_any "Log in" "Login" || true
    sleep 2
    ui_tap_any "Use phone or email" "Phone or email" || true
    sleep 2
    ui_tap_any "Email" "Log in with email" || true
    sleep 2

    ui_tap_any "Email" "Enter email" || adb_tap 800 420
    adb_text "$email"
    sleep 1
    ui_tap_any "Password" || adb_tap 800 520
    adb_text "$password"
    sleep 1
    ui_tap_any "Log in" "Login" "Continue" || adb_tap 800 650
    sleep 6
  fi

  ui_dismiss_popups
  if ui_has_text "For You" || ui_has_text "Home" || ui_has_text "Upload" || ui_has_text "Profile"; then
    if ! ui_has_text "Log in"; then
      accounts_mark_login "$email"
      screenshot_step login-success
      echo "login: success email=$email"
      return 0
    fi
  fi

  screenshot_step login-incomplete
  echo "login: incomplete email=$email"
  return 1
}

flow_login "$@"
