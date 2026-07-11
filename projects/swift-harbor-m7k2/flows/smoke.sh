#!/usr/bin/env bash
# Smoke tests that do not require TikTok signup (no rate limit).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> probe"
./farm.sh probe | grep -q '"ok": true'

echo "==> settings load"
# shellcheck source=lib/paths.sh
source lib/paths.sh
# shellcheck source=lib/settings.sh
source lib/settings.sh
load_settings
[[ -n "$SETTINGS_EMAIL_USER" ]]

echo "==> sample video"
[[ -f assets/sample.mp4 ]]

echo "smoke: ok"
