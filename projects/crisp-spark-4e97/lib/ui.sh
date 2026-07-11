#!/usr/bin/env bash
# UIAutomator helpers — Shell 100% (grep/sed on XML dump).
set -euo pipefail

ui_dump() {
  adb_shell uiautomator dump /sdcard/window_dump.xml >/dev/null 2>&1 || true
  adb_shell cat /sdcard/window_dump.xml >"$UI_DUMP_FILE" 2>/dev/null \
    || adb_pull /sdcard/window_dump.xml "$UI_DUMP_FILE" 2>/dev/null \
    || return 1
  [[ -s "$UI_DUMP_FILE" ]]
}

# Find bounds for first node matching regex on text= or content-desc=
ui_find_bounds() {
  local pattern="$1"
  local line bounds
  ui_dump || return 1
  line="$(grep -oiE "(text|content-desc)=\"[^\"]*${pattern}[^\"]*\"[^>]*bounds=\"\[[0-9]+,[0-9]+\]\[[0-9]+,[0-9]+\]\"" "$UI_DUMP_FILE" | head -1)"
  [[ -n "$line" ]] || return 1
  bounds="$(echo "$line" | grep -oiE 'bounds="\[[0-9]+,[0-9]+\]\[[0-9]+,[0-9]+\]"' | head -1 | sed -E 's/bounds="\[([0-9]+),([0-9]+)\]\[([0-9]+),([0-9]+)\]"/\1 \2 \3 \4/')"
  echo "$bounds"
}

ui_tap_match() {
  local pattern="$1"
  local b x1 y1 x2 y2 cx cy
  b="$(ui_find_bounds "$pattern")" || return 1
  read -r x1 y1 x2 y2 <<<"$b"
  cx=$(( (x1 + x2) / 2 ))
  cy=$(( (y1 + y2) / 2 ))
  echo "ui_tap: $pattern @ ${cx},${cy}" >&2
  adb_tap "$cx" "$cy"
  sleep 1
}

ui_tap_any() {
  local p
  for p in "$@"; do
    if ui_tap_match "$p" 2>/dev/null; then
      return 0
    fi
  done
  return 1
}

ui_has_text() {
  local pattern="$1"
  ui_dump || return 1
  grep -qiE "$pattern" "$UI_DUMP_FILE"
}

ui_wait_text() {
  local pattern="$1"
  local max="${2:-30}"
  local i
  for ((i = 0; i < max; i++)); do
    if ui_has_text "$pattern"; then
      return 0
    fi
    sleep 1
  done
  return 1
}

ui_dismiss_popups() {
  ui_tap_any "Close" "Not now" "Skip" "Cancel" "Later" "OK" "Got it" "Decline" "No thanks" || true
}

ui_scroll_down() {
  adb_swipe 800 1400 800 600 400
  sleep 1
}
