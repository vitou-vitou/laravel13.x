#!/usr/bin/env bash
set -euo pipefail

RESEARCH_ACK_FLAG="--ack-research-only"
RESEARCH_ACK_ENV="TIKTOK_RESEARCH_ACK"

require_research_ack() {
  if [[ "${TIKTOK_RESEARCH_ACK:-}" == "1" ]]; then
    return 0
  fi
  cat >&2 <<EOF
Blocked: signup/cycle require research acknowledgment.

  ./farm.sh signup --ack-research-only
  ./farm.sh cycle --ack-research-only

Or: export TIKTOK_RESEARCH_ACK=1

Read: tools/tiktok-account-creator/docs/CASE_RESEARCH.md
Skill: .agents/skills/tiktok-platform-policy-boundary/SKILL.md
EOF
  return 2
}
