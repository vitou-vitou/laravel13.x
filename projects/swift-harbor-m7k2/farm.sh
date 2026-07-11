#!/usr/bin/env bash
# swift-harbor-m7k2 — Shell 100% TikTok farm window (signup / login / post)
set -euo pipefail

ROOT="$(cd "$(dirname "$0")" && pwd)"
# shellcheck source=lib/paths.sh
source "$ROOT/lib/paths.sh"
# shellcheck source=lib/policy.sh
source "$ROOT/lib/policy.sh"

usage() {
  cat <<EOF
Usage: ./farm.sh <command> [options]

Commands:
  probe              Dry-run signup page locators (visible window)
  signup             Email signup + Gmail OTP
  login              Login with saved or EMAIL/PASSWORD env
  post               Upload assets/sample.mp4
  cycle              signup → login → post (definition of done)
  enter-code         Resume signup with CODE= / arg (after Send code)
  accounts           List data/accounts.json
  smoke              Local smoke tests (no TikTok submit)
  diagnose           Check deps, Tor, Gmail, settings
  new-name           Print a fresh adjective-noun-randomid slug

Options:
  --ack-research-only   Required for signup/cycle (research boundary)
  --keep-open           Leave browser open after flow
  --email ADDR          Override account email
  --password PASS       Override password
  --video PATH          MP4 for post/cycle
  --caption TEXT        Post caption

Env:
  TIKTOK_RESEARCH_ACK=1   Alternative to --ack-research-only
  SETTINGS_FILE           Default: ./settings.json

Setup:
  cp settings.example.json settings.json
  # or: ln -s ../../tools/tiktok-farm-ts/settings.json settings.json
  npm i -g agent-browser && agent-browser install
EOF
}

cmd="${1:-}"
shift || true

KEEP_OPEN=0
EMAIL_ARG=""
PASSWORD_ARG=""
VIDEO_ARG=""
CAPTION_ARG=""
HAS_ACK=0
if [[ "${TIKTOK_RESEARCH_ACK:-}" == "1" ]]; then
  HAS_ACK=1
fi

while [[ $# -gt 0 ]]; do
  case "$1" in
    --keep-open) KEEP_OPEN=1; shift ;;
    --email) EMAIL_ARG="$2"; shift 2 ;;
    --password) PASSWORD_ARG="$2"; shift 2 ;;
    --video) VIDEO_ARG="$2"; shift 2 ;;
    --caption) CAPTION_ARG="$2"; shift 2 ;;
    --ack-research-only) HAS_ACK=1; shift ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Unknown option: $1" >&2; usage; exit 2 ;;
  esac
done

export KEEP_OPEN
[[ -n "$EMAIL_ARG" ]] && export EMAIL="$EMAIL_ARG"
[[ -n "$PASSWORD_ARG" ]] && export PASSWORD="$PASSWORD_ARG"
[[ -n "$VIDEO_ARG" ]] && export VIDEO="$VIDEO_ARG"
[[ -n "$CAPTION_ARG" ]] && export CAPTION="$CAPTION_ARG"

case "$cmd" in
  probe)
    bash "$ROOT/flows/probe.sh"
    ;;
  signup)
    if [[ "$HAS_ACK" -ne 1 ]]; then require_research_ack; fi
    bash "$ROOT/flows/signup.sh"
    ;;
  login)
    bash "$ROOT/flows/login.sh"
    ;;
  post)
    bash "$ROOT/flows/post.sh"
    ;;
  cycle)
    if [[ "$HAS_ACK" -ne 1 ]]; then require_research_ack; fi
    bash "$ROOT/flows/cycle.sh"
    ;;
  accounts)
    # shellcheck source=lib/accounts.sh
    source "$ROOT/lib/accounts.sh"
    accounts_list
    ;;
  smoke)
    bash "$ROOT/flows/smoke.sh"
    ;;
  diagnose)
    bash "$ROOT/flows/diagnose.sh"
    ;;
  enter-code)
    bash "$ROOT/flows/enter-code.sh" "$@"
    ;;
  new-name)
    bash "$ROOT/lib/names.sh"
    ;;
  ""|-h|--help|help)
    usage
    ;;
  *)
    echo "Unknown command: $cmd" >&2
    usage
    exit 2
    ;;
esac
