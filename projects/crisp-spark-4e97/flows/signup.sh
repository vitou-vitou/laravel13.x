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
# shellcheck source=../lib/otp.sh
source "$SCRIPT_DIR/../lib/otp.sh"
# shellcheck source=../lib/accounts.sh
source "$SCRIPT_DIR/../lib/accounts.sh"

BIRTH_MONTH="${BIRTH_MONTH:-January}"
EMAIL="${EMAIL:-}"
KEEP_OPEN="${KEEP_OPEN:-0}"

flow_signup() {
  load_settings
  if ! adb_wait_ready 8; then
    echo "signup: adb unavailable — using touch mode" >&2
    export FARM_MODE=touch
    # shellcheck source=../lib/touch.sh
    source "$SCRIPT_DIR/../lib/touch.sh"
    # shellcheck source=../lib/touch_map.sh
    source "$SCRIPT_DIR/../lib/touch_map.sh"
    local email password
    email="${EMAIL:-$(alias_email farm)}"
    password="$SETTINGS_PASSWORD"
    ld_run_app
    sleep 8
    touch_profile_tab
    sleep 2
    ld_touch 360 700  # Sign up area (approximate)
    sleep 2
    ld_touch 360 550  # Use phone or email
    sleep 2
    ld_touch 360 500  # Email option
    sleep 2
    echo "signup: touch_mode needs ADB for text input — enable ADB per docs/BLOCKERS.md"
    screenshot_step signup-touch-blocked
    return 1
  fi

  local email password send_epoch code
  email="${EMAIL:-$(alias_email farm)}"
  password="$SETTINGS_PASSWORD"

  echo "==> signup $email (LDPlayer TikTok app)"
  ld_kill_app
  sleep 1
  ld_run_app
  sleep 8
  ui_dismiss_popups

  # Profile tab (bottom-right)
  ui_tap_any "Profile" "Me" "profile" || adb_tap 1450 850
  sleep 3
  ui_dismiss_popups

  ui_tap_any "Sign up" "Sign Up" "Create account" || true
  sleep 2
  ui_tap_any "Use phone or email" "Phone or email" "Continue with email" || true
  sleep 2
  ui_tap_any "Email" "Sign up with email" || true
  sleep 2

  # Birthday wheels — tap month/day/year combos (best-effort)
  ui_tap_any "$BIRTH_MONTH" "Month" || true
  ui_tap_any "15" "Day" || true
  ui_tap_any "1995" "Year" || true
  sleep 1

  # Email + password fields
  if ! ui_tap_any "Email" "Enter email"; then
    adb_tap 800 420
  fi
  sleep 0.5
  adb_shell input keyevent 123 >/dev/null 2>&1 || true # move cursor end
  adb_text "$email"
  sleep 1

  ui_tap_any "Password" "Enter password" || adb_tap 800 520
  sleep 0.5
  adb_text "$password"
  sleep 1

  send_epoch="$(date +%s)"
  ui_tap_any "Send code" "Get code" "Continue" "Next" || adb_tap 800 620
  sleep 8
  screenshot_step signup-sent

  if ! code="$(poll_otp "$email" "$send_epoch")"; then
    screenshot_step signup-otp-timeout
    echo "signup: otp_timeout email=$email"
    return 1
  fi

  ui_tap_any "Enter.*code" "6-digit" "Code" || adb_tap 800 420
  sleep 0.5
  adb_text "$code"
  sleep 1
  ui_tap_any "Next" "Continue" "Verify" || adb_tap 800 650
  sleep 6

  # Username step (optional)
  if ui_has_text "Username" || ui_has_text "Create username"; then
    local uname="user_$(printf '%x' $((RANDOM * 65536)))"
    ui_tap_any "Username" || true
    adb_text "$uname"
    ui_tap_any "Next" "Continue" || true
    sleep 4
  fi

  ui_dismiss_popups
  if ui_has_text "For You" || ui_has_text "Home" || ui_has_text "Following"; then
    accounts_upsert "$email" "$password"
    screenshot_step signup-success
    echo "signup: success email=$email"
    return 0
  fi

  screenshot_step signup-incomplete
  echo "signup: incomplete email=$email"
  return 1
}

flow_signup "$@"
