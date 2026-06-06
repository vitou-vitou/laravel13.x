#!/usr/bin/env bash
#
# impeccable-live.sh — Impeccable live mode helpers for dashboard-v1
#
# Live mode is a two-part system:
#   1. Helper server (:8400) + browser picker (live.js) — started by `start`
#   2. Cursor agent poll loop (live-poll.mjs) — MUST run in chat after Go
#
# Without an agent polling, Go shows "Generating 3 variants..." forever.
#
# Usage:
#   ./scripts/impeccable-live.sh start     # Boot/reuse helper + inject live.js
#   ./scripts/impeccable-live.sh status    # Server + pending sessions
#   ./scripts/impeccable-live.sh poll      # Block until browser event (agent use)
#   ./scripts/impeccable-live.sh unstick   # Error-reply all stuck generate sessions
#   ./scripts/impeccable-live.sh stop      # Stop helper; remove inject tag
#
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="${SCRIPT_DIR}/.."
REPO_ROOT="$(cd "${APP_DIR}/../.." && pwd)"

resolve_impeccable_scripts() {
  if [[ -n "${IMPECCABLE_SCRIPTS:-}" && -f "${IMPECCABLE_SCRIPTS}/live.mjs" ]]; then
    echo "${IMPECCABLE_SCRIPTS}"
    return
  fi
  for candidate in \
    "${APP_DIR}/.agents/skills/impeccable/scripts" \
    "${REPO_ROOT}/.cursor/skills/impeccable/scripts" \
    "${HOME}/.agents/skills/impeccable/scripts" \
    "${HOME}/.cursor/skills/impeccable/scripts"
  do
    if [[ -f "${candidate}/live.mjs" ]]; then
      echo "${candidate}"
      return
    fi
  done
  echo "Error: impeccable scripts not found. Set IMPECCABLE_SCRIPTS to scripts/ dir." >&2
  exit 1
}

IMPECCABLE="$(resolve_impeccable_scripts)"
cmd="${1:-status}"

cd "${APP_DIR}"

case "${cmd}" in
  start)
    node "${IMPECCABLE}/live.mjs"
    echo ""
    echo "Next: in Cursor, ask the agent to run impeccable live and keep"
    echo "  ./scripts/impeccable-live.sh poll"
    echo "blocking in the foreground. Then open http://dashboard-v1.test/admin and click Go."
    ;;
  status)
    node "${IMPECCABLE}/live-status.mjs"
    ;;
  poll)
    shift || true
    node "${IMPECCABLE}/live-poll.mjs" "$@"
    ;;
  resume)
    shift || true
    node "${IMPECCABLE}/live-resume.mjs" "$@"
    ;;
  unstick)
    node "${IMPECCABLE}/live-status.mjs" | node -e "
      const chunks = [];
      process.stdin.on('data', d => chunks.push(d));
      process.stdin.on('end', () => {
        const j = JSON.parse(Buffer.concat(chunks).toString());
        const ids = (j.activeSessions || [])
          .filter(s => s.phase === 'generate_requested')
          .map(s => s.id);
        process.stdout.write(ids.join('\n'));
      });
    " | while read -r id; do
      [[ -z "${id}" ]] && continue
      node "${IMPECCABLE}/live-poll.mjs" --reply "${id}" error \
        "Reset: no agent poll loop was running. Restart live mode and poll again."
      echo "Unstuck session ${id}"
    done
    ;;
  stop)
    node "${IMPECCABLE}/live-server.mjs" stop
    ;;
  *)
    echo "Usage: $0 {start|status|poll|resume|unstick|stop}" >&2
    exit 1
    ;;
esac
