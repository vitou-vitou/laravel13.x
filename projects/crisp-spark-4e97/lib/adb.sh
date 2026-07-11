#!/usr/bin/env bash
# ADB bridge for LDPlayer — Shell 100%.
set -euo pipefail

ADB_SERIAL="${ADB_SERIAL:-}"

adb_init() {
  ld_init
  export ADB_BIN
  if [[ -z "$ADB_SERIAL" ]]; then
    ADB_SERIAL="emulator-5554"
    local idx="${LDPLAYER_INDEX:-0}"
    local port=$((5555 + idx * 2))
    ADB_SERIAL="127.0.0.1:${port}"
  fi
  export ADB_SERIAL
}

adb_server_reset() {
  adb_init
  "$ADB_BIN" kill-server 2>/dev/null || true
  sleep 1
  "$ADB_BIN" start-server
}

adb_connect() {
  adb_init
  local port="${1:-5555}"
  adb_server_reset
  "$ADB_BIN" connect "127.0.0.1:${port}" 2>&1 || true
  sleep 1
}

adb_device_ready() {
  adb_init
  local out
  out="$("$ADB_BIN" devices 2>/dev/null)"
  echo "$out" | grep -E 'device$' | grep -qv 'List of devices'
}

adb_wait_ready() {
  local max_wait="${1:-90}"
  local elapsed=0
  echo "adb: waiting for device (max ${max_wait}s)..." >&2
  while [[ "$elapsed" -lt "$max_wait" ]]; do
    if adb_device_ready; then
      echo "adb: device ready" >&2
      return 0
    fi
    adb_connect 5555 || true
    # wake emulator
    ld_console adb --index "$LDPLAYER_INDEX" --command "shell input keyevent KEYCODE_WAKEUP" 2>/dev/null || true
    sleep 2
    elapsed=$((elapsed + 2))
  done
  return 1
}

adb_cmd() {
  adb_init
  if adb_device_ready; then
    "$ADB_BIN" -s "$ADB_SERIAL" "$@"
    return $?
  fi
  ld_console adb --index "$LDPLAYER_INDEX" --command "$*"
}

adb_shell() {
  adb_cmd shell "$@"
}

adb_tap() {
  local x="$1" y="$2"
  adb_shell input tap "$x" "$y"
}

adb_swipe() {
  local x1="$1" y1="$2" x2="$3" y2="$4" dur="${5:-300}"
  adb_shell input swipe "$x1" "$y1" "$x2" "$y2" "$dur"
}

adb_text() {
  local text="$1"
  # Escape spaces for input text
  local escaped="${text// /%s}"
  adb_shell input text "$escaped"
}

adb_key() {
  adb_shell input keyevent "$1"
}

adb_push_file() {
  local local_path="$1" remote_path="$2"
  if adb_device_ready; then
    adb_cmd push "$local_path" "$remote_path"
  else
    ld_push "$local_path" "$remote_path"
  fi
}

ensure_adb() {
  load_settings
  ld_launch
  sleep 2
  ld_set_portrait
  ld_modify_root
  if [[ "${FARM_MODE:-}" == "touch" ]]; then
    echo "adb: skipped (FARM_MODE=touch)" >&2
    return 1
  fi
  if adb_wait_ready "${1:-20}"; then
    return 0
  fi
  echo "adb: not ready — run: ./farm.sh enable-adb" >&2
  return 1
}
