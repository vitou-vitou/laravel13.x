#!/usr/bin/env bash
# LDPlayer operaterecord touch injection (PutMultiTouch coordinate space).
set -euo pipefail

# LDPlayer uses ~32767 virtual coordinate space for operaterecord.
TOUCH_SCALE=32767

pixel_to_virtual() {
  local px="$1" py="$2" width="${3:-720}" height="${4:-1280}"
  local vx vy
  vx=$(( px * TOUCH_SCALE / width ))
  vy=$(( py * TOUCH_SCALE / height ))
  echo "$vx $vy"
}

ld_touch() {
  local px="$1" py="$2"
  local width="${3:-1600}" height="${4:-900}"
  local vx vy json
  read -r vx vy <<<"$(pixel_to_virtual "$px" "$py" "$width" "$height")"
  json="$(cat <<EOF
{"operations":[
  {"timing":0,"operationId":"PutMultiTouch","points":[{"id":1,"x":${vx},"y":${vy},"state":1}]},
  {"timing":50,"operationId":"PutMultiTouch","points":[]},
  {"timing":120,"operationId":"PutMultiTouch","points":[{"id":1,"x":${vx},"y":${vy},"state":0}]},
  {"timing":130,"operationId":"PutMultiTouch","points":[]}
],"recordInfo":{"loopType":2,"loopTimes":1,"circleDuration":200,"loopInterval":0,"loopDuration":0,"accelerateTimes":1,"accelerateTimesEx":1}}
EOF
)"
  ld_console operaterecord --index "$LDPLAYER_INDEX" --content "$json" >/dev/null 2>&1 || true
  sleep 0.5
}
