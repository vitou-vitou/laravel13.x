#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPO_ROOT="$(cd "$PROJECT_ROOT/../.." && pwd)"

export PROJECT_ROOT REPO_ROOT
export SETTINGS_FILE="${SETTINGS_FILE:-$PROJECT_ROOT/settings.json}"
export ACCOUNTS_FILE="${ACCOUNTS_FILE:-$PROJECT_ROOT/data/accounts.json}"
export SAMPLE_VIDEO="${SAMPLE_VIDEO:-$PROJECT_ROOT/assets/sample.mp4}"
export SCREENSHOT_DIR="${SCREENSHOT_DIR:-$PROJECT_ROOT/screenshots}"
export UI_DUMP_FILE="${UI_DUMP_FILE:-/tmp/crisp-spark-ui.xml}"
export LDPLAYER_INDEX="${LDPLAYER_INDEX:-0}"

mkdir -p "$SCREENSHOT_DIR" "$(dirname "$ACCOUNTS_FILE")"
