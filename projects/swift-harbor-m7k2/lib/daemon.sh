#!/usr/bin/env bash
# Force fresh agent-browser daemon (proxy/headed flags apply on first launch).
set -euo pipefail

ab_fresh_daemon() {
  agent-browser close --all 2>/dev/null || true
  sleep 2
  if command -v taskkill >/dev/null 2>&1; then
    taskkill //F //IM "agent-browser.exe" 2>/dev/null || true
  fi
  sleep 1
  unset AB_OPENED
}

ab_setup_proxy() {
  if [[ "${TIKTOK_USE_TOR:-0}" == "1" ]]; then
    if curl -s --max-time 3 -x socks5h://127.0.0.1:9050 https://api.ipify.org >/dev/null 2>&1; then
      export AGENT_BROWSER_PROXY="socks5://127.0.0.1:9050"
      echo "  [proxy] $AGENT_BROWSER_PROXY" >&2
    fi
  fi
}
