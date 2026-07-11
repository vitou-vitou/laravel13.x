#!/usr/bin/env bash
# Project paths — source from farm.sh or flows.
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
REPO_ROOT="$(cd "$PROJECT_ROOT/../.." && pwd)"

export PROJECT_ROOT REPO_ROOT
export SETTINGS_FILE="${SETTINGS_FILE:-$PROJECT_ROOT/settings.json}"
export ACCOUNTS_FILE="${ACCOUNTS_FILE:-$PROJECT_ROOT/data/accounts.json}"
export SAMPLE_VIDEO="${SAMPLE_VIDEO:-$PROJECT_ROOT/assets/sample.mp4}"
export SCREENSHOT_DIR="${SCREENSHOT_DIR:-$PROJECT_ROOT/screenshots}"
export SNAP_FILE="${SNAP_FILE:-/tmp/swift-harbor-snap.txt}"

mkdir -p "$SCREENSHOT_DIR" "$(dirname "$ACCOUNTS_FILE")"
