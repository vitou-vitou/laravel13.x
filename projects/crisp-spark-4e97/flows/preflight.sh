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

flow_preflight() {
  load_settings
  echo "==> preflight LDPlayer index=$LDPLAYER_INDEX home=$SETTINGS_LDPLAYER_HOME"
  ld_launch
  sleep 5
  ld_set_portrait
  ld_modify_root

  local adb_ok=0
  if adb_wait_ready 12; then adb_ok=1; fi

  local pkg="$SETTINGS_TIKTOK_PKG"
  ld_run_app "$pkg" || true
  sleep 5
  screenshot_step preflight-launch

  if [[ "$adb_ok" -eq 1 ]]; then
    if adb_shell pm path "$pkg" 2>/dev/null | grep -q package; then
      adb_push_file "$SAMPLE_VIDEO" /sdcard/Download/farm-sample.mp4 2>/dev/null || true
      echo "preflight: ok (adb + $pkg)"
      return 0
    fi
  fi

  echo "preflight: adb=${adb_ok} — install TikTok in LDPlayer if app did not open"
  echo "preflight: run ./farm.sh enable-adb then re-run preflight"
  echo "preflight: partial ok (emulator running, portrait set)"
  return 0
}

flow_preflight "$@"
