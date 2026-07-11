#!/usr/bin/env bash
# Touch-only TikTok flows when ADB is unavailable (operaterecord PutMultiTouch).
set -euo pipefail

# Virtual resolution after portrait switch (720x1280)
PORTRAIT_W=720
PORTRAIT_H=1280

touch_profile_tab() { ld_touch 650 1180 "$PORTRAIT_W" "$PORTRAIT_H"; }
touch_home_tab() { ld_touch 360 1180 "$PORTRAIT_W" "$PORTRAIT_H"; }
touch_create_tab() { ld_touch 360 1180 "$PORTRAIT_W" "$PORTRAIT_H"; }

touch_dismiss_center() { ld_touch 360 640 "$PORTRAIT_W" "$PORTRAIT_H"; }
