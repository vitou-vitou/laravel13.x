#!/usr/bin/env bash
# crisp-spark-4e97 — Shell 100% LDPlayer TikTok farm (signup / login / post)
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
  preflight          Launch LDPlayer, enable ADB, verify TikTok + sample video
  probe              Dump TikTok app UI readiness (requires ADB)
  signup             Email signup + Gmail OTP (Android TikTok app)
  login              Login saved account
  post               Upload assets/sample.mp4
  cycle              preflight → signup → login → post (definition of done)
  enable-adb         One-time LDPlayer settings UI helper (PowerShell)
  install-tiktok     Open Play Store and search TikTok (touch macros)
  accounts           List data/accounts.json
  new-name           Print adjective-noun-randomid slug

Options:
  --ack-research-only   Required for signup/cycle
  --email ADDR
  --password PASS
  --video PATH
  --caption TEXT

Env:
  TIKTOK_RESEARCH_ACK=1
  LDPLAYER_HOME         Override auto-detect (default: D:/LDPlayer/LDPlayer9)
  LDPLAYER_INDEX        Emulator index (default: 0)

Setup:
  cp settings.example.json settings.json
  Install TikTok in LDPlayer (Play Store)
  ./farm.sh preflight
EOF
}

cmd="${1:-}"
shift || true

EMAIL_ARG=""
PASSWORD_ARG=""
VIDEO_ARG=""
CAPTION_ARG=""
HAS_ACK=0
[[ "${TIKTOK_RESEARCH_ACK:-}" == "1" ]] && HAS_ACK=1

while [[ $# -gt 0 ]]; do
  case "$1" in
    --keep-open) shift ;;
    --email) EMAIL_ARG="$2"; shift 2 ;;
    --password) PASSWORD_ARG="$2"; shift 2 ;;
    --video) VIDEO_ARG="$2"; shift 2 ;;
    --caption) CAPTION_ARG="$2"; shift 2 ;;
    --ack-research-only) HAS_ACK=1; shift ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Unknown option: $1" >&2; usage; exit 2 ;;
  esac
done

[[ -n "$EMAIL_ARG" ]] && export EMAIL="$EMAIL_ARG"
[[ -n "$PASSWORD_ARG" ]] && export PASSWORD="$PASSWORD_ARG"
[[ -n "$VIDEO_ARG" ]] && export VIDEO="$VIDEO_ARG"
[[ -n "$CAPTION_ARG" ]] && export CAPTION="$CAPTION_ARG"

case "$cmd" in
  preflight) bash "$ROOT/flows/preflight.sh" ;;
  probe) bash "$ROOT/flows/probe.sh" ;;
  signup)
    [[ "$HAS_ACK" -eq 1 ]] || require_research_ack
    bash "$ROOT/flows/signup.sh"
    ;;
  login) bash "$ROOT/flows/login.sh" ;;
  post) bash "$ROOT/flows/post.sh" ;;
  cycle)
    [[ "$HAS_ACK" -eq 1 ]] || require_research_ack
    bash "$ROOT/flows/cycle.sh"
    ;;
  enable-adb)
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File "$ROOT/scripts/enable_adb.ps1"
    ;;
  install-tiktok) bash "$ROOT/flows/install_tiktok.sh" ;;
  accounts)
    # shellcheck source=lib/accounts.sh
    source "$ROOT/lib/accounts.sh"
    accounts_list
    ;;
  new-name) bash "$ROOT/lib/names.sh" ;;
  ""|-h|--help|help) usage ;;
  *)
    echo "Unknown command: $cmd" >&2
    usage
    exit 2
    ;;
esac
