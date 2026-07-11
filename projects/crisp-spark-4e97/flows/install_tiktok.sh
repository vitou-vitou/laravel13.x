#!/usr/bin/env bash
# Install TikTok via LDStore / Play Store using operaterecord taps (no ADB required).
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=../lib/paths.sh
source "$SCRIPT_DIR/../lib/paths.sh"
# shellcheck source=../lib/settings.sh
source "$SCRIPT_DIR/../lib/settings.sh"
# shellcheck source=../lib/ldplayer.sh
source "$SCRIPT_DIR/../lib/ldplayer.sh"
# shellcheck source=../lib/touch.sh
source "$SCRIPT_DIR/../lib/touch.sh"

flow_install_tiktok() {
  load_settings
  ld_launch
  sleep 5

  local pkg="$SETTINGS_TIKTOK_PKG"
  echo "==> install TikTok ($pkg)"

  # Try LDPlayer store install by package name
  if ld_console installapp --index "$LDPLAYER_INDEX" --packagename "$pkg" 2>/dev/null; then
    sleep 15
    echo "install: requested via installapp packagename"
  fi

  # Open System Apps folder (center-left icon)
  ld_touch 120 380
  sleep 2
  # Play Store inside folder (first row)
  ld_touch 200 350
  sleep 5

  # Search box
  ld_touch 800 120
  sleep 1
  # Type via LDPlayer keyboard - use operaterecord text if available; fallback adb later
  ld_console action --index "$LDPLAYER_INDEX" --key call.keyboard --value "TikTok" 2>/dev/null || true
  sleep 2
  ld_touch 800 250
  sleep 3
  # Install button area
  ld_touch 800 450
  sleep 30

  screenshot_step install-tiktok
  echo "install: manual confirm in LDPlayer if Play Store install did not auto-complete"
  echo "install: then run ./farm.sh preflight"
}

flow_install_tiktok "$@"
