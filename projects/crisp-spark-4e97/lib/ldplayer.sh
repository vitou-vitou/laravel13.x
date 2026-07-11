#!/usr/bin/env bash
# LDPlayer ldconsole wrapper — Shell 100%.
set -euo pipefail

resolve_ldplayer_home() {
  local candidates=()
  if [[ -n "${SETTINGS_LDPLAYER_HOME:-}" ]]; then
    candidates+=("$SETTINGS_LDPLAYER_HOME")
  fi
  if [[ -n "${LDPLAYER_HOME:-}" ]]; then
    candidates+=("$LDPLAYER_HOME")
  fi
  candidates+=(
    "/d/LDPlayer/LDPlayer9"
    "/c/Program Files/LDPlayer/LDPlayer9"
    "/c/Program Files (x86)/LDPlayer/LDPlayer9"
    "/d/LDPlayer/LDPlayer4.0"
  )
  local c
  for c in "${candidates[@]}"; do
    c="${c//\\//}"
    if [[ -x "$c/ldconsole.exe" ]]; then
      echo "$c"
      return 0
    fi
  done
  echo "LDPlayer not found. Set ldplayerHome in settings.json" >&2
  return 1
}

ld_init() {
  LDPLAYER_HOME="$(resolve_ldplayer_home)"
  export LDPLAYER_HOME
  LDCONSOLE="$LDPLAYER_HOME/ldconsole.exe"
  ADB_BIN="$LDPLAYER_HOME/adb.exe"
  export LDCONSOLE ADB_BIN
  LDPLAYER_INDEX="${LDPLAYER_INDEX:-0}"
}

ld_console() {
  ld_init
  "$LDCONSOLE" "$@"
}

ld_is_running() {
  ld_init
  "$LDCONSOLE" isrunning --index "$LDPLAYER_INDEX" 2>/dev/null | grep -q running
}

ld_launch() {
  ld_init
  if ld_is_running; then
    echo "ldplayer: already running index=$LDPLAYER_INDEX" >&2
    return 0
  fi
  echo "ldplayer: launching index=$LDPLAYER_INDEX" >&2
  "$LDCONSOLE" launch --index "$LDPLAYER_INDEX"
}

ld_reboot() {
  ld_init
  echo "ldplayer: reboot index=$LDPLAYER_INDEX" >&2
  "$LDCONSOLE" reboot --index "$LDPLAYER_INDEX"
}

ld_modify_root() {
  ld_init
  "$LDCONSOLE" modify --index "$LDPLAYER_INDEX" --root 1 2>/dev/null || true
}

ld_set_portrait() {
  ld_init
  # TikTok Android UI is portrait-first
  "$LDCONSOLE" modify --index "$LDPLAYER_INDEX" --resolution 720,1280,320 2>/dev/null || true
}

ld_run_app() {
  local pkg="${1:-$SETTINGS_TIKTOK_PKG}"
  ld_init
  "$LDCONSOLE" runapp --index "$LDPLAYER_INDEX" --packagename "$pkg"
}

ld_kill_app() {
  local pkg="${1:-$SETTINGS_TIKTOK_PKG}"
  ld_init
  "$LDCONSOLE" killapp --index "$LDPLAYER_INDEX" --packagename "$pkg" 2>/dev/null || true
}

ld_push() {
  local local_path="$1" remote_path="$2"
  ld_init
  local win_local="${local_path//\//\\}"
  "$LDCONSOLE" push --index "$LDPLAYER_INDEX" --local "$win_local" --remote "$remote_path"
}

ld_pull() {
  local remote_path="$1" local_path="$2"
  ld_init
  local win_local="${local_path//\//\\}"
  "$LDCONSOLE" pull --index "$LDPLAYER_INDEX" --remote "$remote_path" --local "$win_local"
}

ld_list2_line() {
  ld_init
  "$LDCONSOLE" list2 2>/dev/null | grep "^${LDPLAYER_INDEX}," | head -1
}

ld_window_rect() {
  # list2: index,name,top_hwnd,bind_hwnd,running,pid,vbox_pid,w,h,dpi
  local line left=159 top=49 right=1799 bottom=984
  line="$(ld_list2_line || true)"
  if [[ -n "$line" ]]; then
    : # hwnd available for PowerShell scripts
  fi
  echo "$left $top $right $bottom"
}

ld_screenshot_host() {
  local out="$1"
  ld_init
  local win_out="${out//\//\\}"
  if "$LDCONSOLE" scan --index "$LDPLAYER_INDEX" --file "$win_out" 2>/dev/null; then
    [[ -f "$out" ]] && return 0
  fi
  # Fallback: PowerShell host capture
  if [[ -x "$PROJECT_ROOT/scripts/capture_window.ps1" ]]; then
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$PROJECT_ROOT/scripts/capture_window.ps1" -OutPath "$win_out" || true
  fi
  [[ -f "$out" ]]
}

screenshot_step() {
  local name="$1"
  local path="$SCREENSHOT_DIR/${name}-$(date +%s).png"
  ld_screenshot_host "$path" || true
  echo "$path"
  return 0
}
