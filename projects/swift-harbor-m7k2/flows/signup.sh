#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=../lib/paths.sh
source "$SCRIPT_DIR/../lib/paths.sh"
# shellcheck source=../lib/settings.sh
source "$SCRIPT_DIR/../lib/settings.sh"
# shellcheck source=../lib/browser.sh
source "$SCRIPT_DIR/../lib/browser.sh"
# shellcheck source=../lib/otp.sh
source "$SCRIPT_DIR/../lib/otp.sh"
# shellcheck source=../lib/accounts.sh
source "$SCRIPT_DIR/../lib/accounts.sh"

BIRTH_MONTH="${BIRTH_MONTH:-January}"
BIRTH_DAY="${BIRTH_DAY:-15}"
BIRTH_YEAR="${BIRTH_YEAR:-1995}"
EMAIL="${EMAIL:-}"
KEEP_OPEN="${KEEP_OPEN:-0}"

flow_signup() {
  require_agent_browser
  load_settings

  local email password send_epoch
  email="${EMAIL:-$(alias_email farm)}"
  password="$SETTINGS_PASSWORD"

  browser_open_session "$email"
  echo "==> signup $email"

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

  pick_combobox_option 'combobox.*Month' "$BIRTH_MONTH" || pick_combobox_option 'Month' "$BIRTH_MONTH" || true
  pick_combobox_option 'combobox.*Day' "$BIRTH_DAY" || pick_combobox_option 'Day' "$BIRTH_DAY" || true
  pick_combobox_option 'combobox.*Year' "$BIRTH_YEAR" || pick_combobox_option 'Year' "$BIRTH_YEAR" || true

  find_ref_fill 'textbox.*Email' "$email"
  find_ref_fill 'textbox.*Password' "$password"

  local check_ref
  check_ref="$(find_ref 'checkbox.*trending' || find_ref 'checkbox' || true)"
  if [[ -n "$check_ref" ]]; then
    ab check "@$check_ref" 2>/dev/null || ab click "@$check_ref" || true
  fi

  send_epoch="$(date +%s)"
  find_ref_click 'button.*Send code'
  ab wait 3000
  screenshot_step signup-sent

  # Resend once if OTP field still looks idle after 15s
  ab_snapshot
  if ! grep -q 'textbox.*6-digit' "$SNAP_FILE" 2>/dev/null; then
    find_ref_click 'button.*Send code' 2>/dev/null || true
    ab wait 5000
  fi

  local code=""
  if ! code="$(poll_otp "$email" "$send_epoch")"; then
    screenshot_step signup-otp-timeout
    echo "signup: otp_timeout email=$email"
    [[ "$KEEP_OPEN" == "1" ]] || browser_close
    return 1
  fi

  find_ref_fill 'textbox.*6-digit' "$code"
  ab wait 500

  body="$(page_text)"
  if echo "$body" | grep -qi 'Maximum number of attempts'; then
    screenshot_step signup-rate-limited
    echo "signup: rate_limited email=$email — wait 30-60 min or rotate IP (./bin/tiktok-proxy-ensure)"
    [[ "$KEEP_OPEN" == "1" ]] || browser_close
    return 1
  fi

  find_ref_click 'button.*Next' || find_ref_click 'button.*Continue' || true
  ab wait 5000

  handle_username_step || true
  ab wait 3000

  local url
  url="$(current_url)"
  if signup_progressed "$url"; then
    accounts_upsert "$email" "$password"
    screenshot_step signup-success
    echo "signup: success email=$email"
    [[ "$KEEP_OPEN" == "1" ]] || browser_close
    return 0
  fi

  screenshot_step signup-incomplete
  echo "signup: incomplete url=$url email=$email"
  [[ "$KEEP_OPEN" == "1" ]] || browser_close
  return 1
}

flow_signup "$@"
