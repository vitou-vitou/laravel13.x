#!/usr/bin/env bash
set -euo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=../lib/paths.sh
source "$SCRIPT_DIR/../lib/paths.sh"

CAPTION="${CAPTION:-crisp-spark cycle #tiktok}"
VIDEO="${VIDEO:-$SAMPLE_VIDEO}"

echo "=== 0/3 preflight ==="
bash "$SCRIPT_DIR/preflight.sh" || {
  echo "cycle stopped at preflight"
  exit 1
}

echo "=== 1/3 signup ==="
if ! bash "$SCRIPT_DIR/signup.sh"; then
  echo "cycle stopped at signup"
  exit 1
fi

echo "=== 2/3 login ==="
if ! bash "$SCRIPT_DIR/login.sh"; then
  echo "cycle stopped at login"
  exit 1
fi

echo "=== 3/3 post ==="
CAPTION="$CAPTION" VIDEO="$VIDEO" bash "$SCRIPT_DIR/post.sh" || {
  echo "cycle stopped at post"
  exit 1
}

echo "cycle: success (signup + login + post via LDPlayer)"
