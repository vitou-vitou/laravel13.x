#!/usr/bin/env bash
# Resume signup after Send code — pass CODE env or first arg.
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
CODE="${1:-${CODE:-}}"
KEEP_OPEN="${KEEP_OPEN:-0}"

[[ -n "$EMAIL" ]] || { echo "EMAIL required" >&2; exit 2; }
[[ -n "$CODE" ]] || { echo "CODE required (arg or env)" >&2; exit 2; }

load_settings
browser_open_session "$EMAIL"
echo "==> enter-code $EMAIL"

find_ref_fill 'textbox.*6-digit' "$CODE"
ab wait 500

body="$(page_text)"
if echo "$body" | grep -qi 'Maximum number of attempts'; then
  echo "signup: rate_limited"
  exit 1
fi

find_ref_click 'button.*Next' || find_ref_click 'button.*Continue' || true
ab wait 5000
handle_username_step || true

url="$(current_url)"
if signup_progressed "$url"; then
  accounts_upsert "$EMAIL" "$SETTINGS_PASSWORD"
  echo "signup: success email=$EMAIL"
  [[ "$KEEP_OPEN" == "1" ]] || browser_close
  exit 0
fi

echo "signup: incomplete url=$url"
exit 1
