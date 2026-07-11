#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=../lib/paths.sh
source "$SCRIPT_DIR/../lib/paths.sh"
# shellcheck source=../lib/settings.sh
source "$SCRIPT_DIR/../lib/settings.sh"
# shellcheck source=../lib/browser.sh
source "$SCRIPT_DIR/../lib/browser.sh"
# shellcheck source=../lib/accounts.sh
source "$SCRIPT_DIR/../lib/accounts.sh"

EMAIL="${EMAIL:-}"
PASSWORD="${PASSWORD:-}"
KEEP_OPEN="${KEEP_OPEN:-0}"

flow_login() {
  require_agent_browser
  load_settings

  local email password
  email="${EMAIL:-$(accounts_latest_email)}"
  password="${PASSWORD:-$(accounts_latest_password)}"

  if [[ -z "$email" || -z "$password" ]]; then
    echo "login: no_account — run signup first or set EMAIL/PASSWORD" >&2
    return 2
  fi

  browser_open_session "$email"
  echo "==> login $email"

  ab open "https://www.tiktok.com/login/phone-or-email/email"
  ab wait --load networkidle
  dismiss_cookies || true

  find_ref_click 'Use phone or email' 2>/dev/null || true
  ab wait 1000
  find_ref_click 'Log in with email' 2>/dev/null || find_ref_click 'Sign up with email' 2>/dev/null || true
  ab wait 800

  find_ref_fill 'textbox.*Email or username' "$email" \
    || find_ref_fill 'textbox.*Email' "$email" \
    || find_ref_fill 'textbox.*[Uu]sername' "$email"
  find_ref_fill 'textbox.*Password' "$password"

  local body
  body="$(page_text)"
  if echo "$body" | grep -qi 'Maximum number of attempts'; then
    echo "login: rate_limited email=$email"
    screenshot_step login-rate-limited
    [[ "$KEEP_OPEN" == "1" ]] || browser_close
    return 1
  fi

  find_ref_click 'button.*Log in' || find_ref_click 'button.*Login' || find_ref_click 'button.*Continue' || true
  ab wait 5000

  local url body
  url="$(current_url)"
  if is_logged_in "$url"; then
    accounts_mark_login "$email"
    screenshot_step login-success
    echo "login: success email=$email"
    [[ "$KEEP_OPEN" == "1" ]] || browser_close
    return 0
  fi

  body="$(page_text)"
  if echo "$body" | grep -qi verify; then
    echo "login: verification_required email=$email"
    [[ "$KEEP_OPEN" == "1" ]] || browser_close
    return 1
  fi
  if echo "$body" | grep -qi 'incorrect\|wrong password'; then
    echo "login: bad_credentials email=$email"
    [[ "$KEEP_OPEN" == "1" ]] || browser_close
    return 1
  fi

  screenshot_step login-incomplete
  echo "login: incomplete url=$url email=$email"
  [[ "$KEEP_OPEN" == "1" ]] || browser_close
  return 1
}

flow_login "$@"
