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
VIDEO="${VIDEO:-$SAMPLE_VIDEO}"
CAPTION="${CAPTION:-swift-harbor farm test #tiktok}"
KEEP_OPEN="${KEEP_OPEN:-0}"

flow_post() {
  require_agent_browser
  load_settings

  local email password
  email="${EMAIL:-$(accounts_latest_email)}"
  password="${PASSWORD:-$(accounts_latest_password)}"

  if [[ -z "$email" || -z "$password" ]]; then
    echo "post: no_account — run signup first" >&2
    return 2
  fi
  if [[ ! -f "$VIDEO" ]]; then
    echo "post: missing_video $VIDEO" >&2
    return 2
  fi

  browser_open_session "$email"
  echo "==> post $email video=$VIDEO"

  local upload_url
  for upload_url in \
    "https://www.tiktok.com/tiktokstudio/upload?from=web" \
    "https://www.tiktok.com/upload" \
    "https://www.tiktok.com/creator-center/upload"; do
    ab open "$upload_url"
    ab wait --load networkidle
    dismiss_cookies || true
    ab wait 2000

    local url
    url="$(current_url)"
    if ! is_logged_in "$url"; then
      echo "  not logged in — running login sub-flow"
      browser_close
      EMAIL="$email" PASSWORD="$password" KEEP_OPEN=1 bash "$SCRIPT_DIR/login.sh" || true
      browser_open_session "$email"
      ab open "$upload_url"
      ab wait --load networkidle
      ab wait 2000
    fi

    ab_snapshot
    local file_ref
    file_ref="$(grep 'input.*type=.file' "$SNAP_FILE" | grep -oE 'ref=e[0-9]+' | head -1 | cut -d= -f2)"
    if [[ -z "$file_ref" ]]; then
      file_ref="$(grep -i 'upload' "$SNAP_FILE" | grep -oE 'ref=e[0-9]+' | head -1 | cut -d= -f2)"
    fi
    if [[ -n "$file_ref" ]]; then
      ab upload "@$file_ref" "$VIDEO"
      break
    fi
  done

  ab wait 5000
  screenshot_step post-uploaded

  local cap_ref
  cap_ref="$(find_ref 'textbox' || find_ref 'contenteditable' || true)"
  if [[ -n "$cap_ref" ]]; then
    ab fill "@$cap_ref" "$CAPTION" || ab click "@$cap_ref" && ab keyboard inserttext "$CAPTION" || true
  fi
  ab wait 1500

  local posted=0
  for label in Post Publish Upload; do
    if find_ref_click "button \"$label\"" 2>/dev/null; then
      posted=1
      break
    fi
  done
  if [[ "$posted" -eq 0 ]]; then
    find_ref_click 'button.*Post' || true
  fi

  local i url body
  for ((i = 0; i < 20; i++)); do
    ab wait 3000
    url="$(current_url)"
    body="$(page_text)"
    if [[ "$url" == *"/video/"* ]] || echo "$body" | grep -qiE 'uploaded|being processed|video published|manage your posts'; then
      accounts_mark_post "$email"
      screenshot_step post-success
      echo "post: success email=$email"
      [[ "$KEEP_OPEN" == "1" ]] || browser_close
      return 0
    fi
  done

  screenshot_step post-timeout
  echo "post: post_timeout email=$email"
  [[ "$KEEP_OPEN" == "1" ]] || browser_close
  return 1
}

flow_post "$@"
