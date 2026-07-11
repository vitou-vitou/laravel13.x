#!/usr/bin/env bash
# agent-browser helpers — visible window, per-account session.
set -euo pipefail

# shellcheck source=daemon.sh
source "$(dirname "${BASH_SOURCE[0]}")/daemon.sh"

require_agent_browser() {
  if ! command -v agent-browser >/dev/null 2>&1; then
    echo "Install: npm install -g agent-browser && agent-browser install" >&2
    return 1
  fi
}

session_name_for_email() {
  local email="$1"
  local slug
  slug="$(echo "$email" | tr '@+.' '___' | tr '[:upper:]' '[:lower:]')"
  echo "swift-harbor-${slug}"
}

ab() {
  export AGENT_BROWSER_HEADED="${AGENT_BROWSER_HEADED:-true}"
  if [[ -z "${AB_OPENED:-}" ]]; then
    ab_setup_proxy
    export AB_OPENED=1
  fi
  agent-browser --headed --session "$AB_SESSION" "$@"
}

ab_snapshot() {
  ab snapshot -i >"$SNAP_FILE"
}

find_ref() {
  local pattern="$1"
  ab_snapshot
  grep -E "$pattern" "$SNAP_FILE" | grep -oE 'ref=e[0-9]+' | head -1 | cut -d= -f2
}

find_ref_click() {
  local pattern="$1"
  local ref
  ref="$(find_ref "$pattern")"
  if [[ -z "$ref" ]]; then
    echo "Element not found: $pattern" >&2
    cat "$SNAP_FILE" >&2
    return 1
  fi
  ab click "@$ref"
}

find_ref_fill() {
  local pattern="$1"
  local value="$2"
  local ref
  ref="$(find_ref "$pattern")"
  [[ -n "$ref" ]] || { echo "Fill target not found: $pattern" >&2; return 1; }
  ab fill "@$ref" "$value"
}

pick_combobox_option() {
  local label_pattern="$1"
  local option="$2"
  local ref
  ref="$(find_ref "$label_pattern")"
  [[ -n "$ref" ]] || { echo "Combobox not found: $label_pattern" >&2; return 1; }
  ab click "@$ref"
  ab wait 400
  ab_snapshot
  local opt_ref
  opt_ref="$(grep "option \"$option\"" "$SNAP_FILE" | grep -oE 'ref=e[0-9]+' | head -1 | cut -d= -f2)"
  if [[ -z "$opt_ref" ]]; then
    opt_ref="$(grep -F "\"$option\"" "$SNAP_FILE" | grep -oE 'ref=e[0-9]+' | head -1 | cut -d= -f2)"
  fi
  [[ -n "$opt_ref" ]] || { echo "Option not found: $option" >&2; cat "$SNAP_FILE" >&2; return 1; }
  ab click "@$opt_ref"
  ab wait 300
}

dismiss_cookies() {
  for label in "Decline optional cookies" "Reject all" "Decline all" "Only allow essential"; do
    local ref
    ref="$(find_ref "button \"$label\"" || true)"
    if [[ -n "$ref" ]]; then
      ab click "@$ref" || true
      ab wait 500
      return 0
    fi
  done
}

current_url() {
  local raw
  raw="$(ab url 2>/dev/null | tail -1 || ab eval "location.href" 2>/dev/null | tail -1)"
  raw="${raw%\"}"
  raw="${raw#\"}"
  echo "$raw"
}

page_text() {
  ab text body 2>/dev/null || true
}

screenshot_step() {
  local name="$1"
  ab screenshot "$SCREENSHOT_DIR/${name}-$(date +%s).png" 2>/dev/null || true
}

browser_open_session() {
  local email="${1:-default}"
  ab_fresh_daemon
  ab_setup_proxy
  export AB_SESSION AB_OPENED=1
  AB_SESSION="$(session_name_for_email "$email")"
}

browser_close() {
  ab close 2>/dev/null || true
}

signup_progressed() {
  local url="$1"
  [[ "$url" == *"/signup/phone-or-email/email"* ]] && return 1
  [[ "$url" == *"/signup/create-username"* ]] && return 0
  [[ "$url" == *"/foryou"* ]] && return 0
  [[ "$url" == *"/login/download-app"* ]] && return 0
  [[ "$url" == *"islands/tiktok_web"* ]] && return 0
  return 1
}

is_logged_in() {
  local url="${1:-}"
  url="$(echo "$url" | tr '[:upper:]' '[:lower:]')"
  [[ "$url" == *"/foryou"* ]] && return 0
  [[ "$url" == *"/following"* ]] && return 0
  [[ "$url" == *"/upload"* ]] && return 0
  [[ "$url" == *"tiktok.com/@"* ]] && return 0
  [[ "$url" == *"/tiktokstudio"* ]] && return 0
  [[ "$url" == *"/login"* ]] && return 1
  return 1
}

handle_username_step() {
  local url
  url="$(current_url)"
  [[ "$url" == *"/signup/create-username"* ]] || return 0
  local uname="user_$(printf '%x' $((RANDOM * 65536)))"
  find_ref_fill 'textbox.*[Uu]sername' "$uname" || find_ref_fill 'input.*username' "$uname" || true
  for label in "Next" "Continue" "Sign up" "Skip"; do
    local ref
    ref="$(find_ref "button \"$label\"" || true)"
    if [[ -n "$ref" ]]; then
      ab click "@$ref"
      ab wait 1500
      return 0
    fi
  done
}
