#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
# shellcheck source=../lib/paths.sh
source "$ROOT/lib/paths.sh"
# shellcheck source=../lib/settings.sh
source "$ROOT/lib/settings.sh"

echo "=== swift-harbor-m7k2 diagnose ==="

if command -v agent-browser >/dev/null 2>&1; then
  echo "agent-browser: OK"
else
  echo "agent-browser: MISSING (npm i -g agent-browser && agent-browser install)"
fi

if [[ -f "$SETTINGS_FILE" ]]; then
  load_settings
  echo "settings: OK ($SETTINGS_EMAIL_USER@$SETTINGS_EMAIL_DOMAIN)"
else
  echo "settings: MISSING ($SETTINGS_FILE)"
fi

if command -v py >/dev/null 2>&1; then
  if py -3 -c "import imaplib" 2>/dev/null; then
    echo "python+imap: OK"
  fi
fi

if curl -s --max-time 3 -x socks5h://127.0.0.1:9050 https://api.ipify.org >/dev/null 2>&1; then
  ip="$(curl -s --max-time 3 -x socks5h://127.0.0.1:9050 https://api.ipify.org)"
  echo "tor: OK (exit $ip) — set TIKTOK_USE_TOR=1 to route browser via Tor (slow)"
else
  echo "tor: not running (optional)"
fi

if [[ -x "$REPO_ROOT/bin/gmail-tiktok-code" ]]; then
  if "$REPO_ROOT/bin/gmail-tiktok-code" --once 2>/dev/null; then
    echo "gws-gmail: OK (code available)"
  else
    echo "gws-gmail: not authed (./bin/gws-gmail-prep) — IMAP fallback used"
  fi
fi

echo "sample video: $([[ -f $SAMPLE_VIDEO ]] && echo OK || echo MISSING)"
echo ""
echo "Quick probe: ./farm.sh probe"
