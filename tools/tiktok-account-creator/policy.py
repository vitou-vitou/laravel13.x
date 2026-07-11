"""
TikTok platform policy preflight for local research tooling.

Not legal advice. Mirrors boundaries in tools/tiktok-metadata and CASE_RESEARCH.md.
"""

from __future__ import annotations

import os
import sys
from dataclasses import dataclass

TIKTOK_TOS_URL = "https://www.tiktok.com/legal/terms-of-service"
COMMUNITY_GUIDELINES_URL = "https://www.tiktok.com/community-guidelines"
METADATA_TOOL_PATH = "tools/tiktok-metadata"
HUMAN_SIGNUP_SCRIPT = "bin/tiktok-signup-run"

RESEARCH_ACK_ENV = "TIKTOK_RESEARCH_ACK"
RESEARCH_ACK_FLAG = "--ack-research-only"


@dataclass(frozen=True)
class PolicyCheck:
    ok: bool
    message: str
    blocked_reason: str | None = None


POLICY_SUMMARY = f"""
TikTok account automation — policy boundary (research repo)
------------------------------------------------------------
• Bulk or automated account creation violates TikTok Terms of Service:
  {TIKTOK_TOS_URL}
• This tool is for **local research / education** only — not production bulk signup.
• Allowed alternative in this repo: {METADATA_TOOL_PATH} (creator consent, metadata-only).
• Human-in-loop single signup: ./{HUMAN_SIGNUP_SCRIPT}
• Respect rate limits; audit runs via runs.csv (see run_log.py).

To run the browser bot you must pass {RESEARCH_ACK_FLAG} or set {RESEARCH_ACK_ENV}=1.
""".strip()


def research_acknowledged(argv: list[str] | None = None) -> bool:
    if os.environ.get(RESEARCH_ACK_ENV, "").strip() in ("1", "true", "yes"):
        return True
    args = argv if argv is not None else sys.argv
    return RESEARCH_ACK_FLAG in args


def preflight(
    *,
    dry_run: bool = False,
    argv: list[str] | None = None,
    max_accounts_per_day: int | None = None,
    accounts_created_today: int = 0,
) -> PolicyCheck:
    if dry_run:
        return PolicyCheck(ok=True, message="Dry run — policy gate skipped for locators only.")

    if not research_acknowledged(argv):
        return PolicyCheck(
            ok=False,
            message=POLICY_SUMMARY,
            blocked_reason="missing_research_ack",
        )

    if max_accounts_per_day is not None and accounts_created_today >= max_accounts_per_day:
        return PolicyCheck(
            ok=False,
            message=(
                f"Daily research cap reached ({accounts_created_today}/"
                f"{max_accounts_per_day}). Stop for today or lower volume."
            ),
            blocked_reason="daily_cap",
        )

    return PolicyCheck(
        ok=True,
        message="Research-only acknowledgment recorded. Proceed with minimal volume.",
    )


def compliance_checklist() -> list[str]:
    """Checklist aligned with Legal Compliance Checker + tiktok-metadata README."""
    return [
        "Confirm use case is local research or single-account testing — not bulk production.",
        f"Read TikTok ToS: {TIKTOK_TOS_URL}",
        f"Prefer authorized metadata ops ({METADATA_TOOL_PATH}) when creator consent exists.",
        "Enable run logging (runs.csv) before each session.",
        "After Send code, verify mail from account.tiktok before assuming IMAP failure.",
        "On rate limit, wait 30–60+ minutes; do not hammer resend.",
        "Keep settings.json and users.txt out of git.",
        "Delete only TikTok verification mail (never whole inbox).",
    ]
