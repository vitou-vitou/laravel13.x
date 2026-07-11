#!/usr/bin/env bash
# TikTok research boundary — mirrors tools/tiktok-account-creator/policy.py
set -euo pipefail

RESEARCH_ACK_FLAG="--ack-research-only"
RESEARCH_ACK_ENV="TIKTOK_RESEARCH_ACK"

require_research_ack() {
  local argc="$#"
  local i
  for ((i = 0; i < argc; i++)); do
    if [[ "${!i}" == "$RESEARCH_ACK_FLAG" ]]; then
      return 0
    fi
  done
  if [[ "${!RESEARCH_ACK_ENV:-}" == "1" ]]; then
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
