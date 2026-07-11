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
VIDEO="${VIDEO:-$SAMPLE_VIDEO}"
CAPTION="${CAPTION:-crisp-spark farm test #tiktok}"
REMOTE_VIDEO="/sdcard/Download/farm-sample.mp4"

flow_post() {
  load_settings
  adb_wait_ready 8 || { echo "post: adb_unavailable"; return 1; }

  local email
  email="${EMAIL:-$(accounts_latest_email)}"
  if [[ -z "$email" ]]; then
    echo "post: no_account" >&2
    return 2
  fi
  if [[ ! -f "$VIDEO" ]]; then
    echo "post: missing_video $VIDEO" >&2
    return 2
  fi

  echo "==> post $email video=$VIDEO"
  adb_push_file "$VIDEO" "$REMOTE_VIDEO"
  ld_kill_app
  sleep 1
  ld_run_app
  sleep 8
  ui_dismiss_popups

  # Ensure logged in
  if ! bash "$SCRIPT_DIR/login.sh"; then
    echo "post: login_required" >&2
    return 1
  fi

  # Create (+) tab center-bottom
  ui_tap_any "Create" "\\+" || adb_tap 800 850
  sleep 3
  ui_tap_any "Upload" "Gallery" "Videos" || true
  sleep 2

  # Pick first video / farm-sample
  ui_tap_any "farm-sample" "Download" "Gallery" || adb_tap 400 500
  sleep 2
  ui_tap_any "Next" "Continue" || adb_tap 1400 850
  sleep 4
  ui_tap_any "Next" "Continue" || adb_tap 1400 850
  sleep 3

  ui_tap_any "Describe" "caption" "Add description" || adb_tap 500 200
  sleep 0.5
  adb_text "$CAPTION"
  sleep 1

  ui_tap_any "Post" "Publish" || adb_tap 1200 100
  sleep 8

  local i
  for ((i = 0; i < 15; i++)); do
    if ui_has_text "uploaded" || ui_has_text "View profile" || ui_has_text "Your video"; then
      accounts_mark_post "$email"
      screenshot_step post-success
      echo "post: success email=$email"
      return 0
    fi
    sleep 3
  done

  screenshot_step post-timeout
  echo "post: post_timeout email=$email"
  return 1
}

flow_post "$@"
