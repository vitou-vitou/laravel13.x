"""
Living directive — GOLD stays fixed; plan and issue log update each loop.

User direction: solve signup 100% (10 accounts in users.txt). How changes; goal does not.
"""

from __future__ import annotations

from datetime import datetime, timezone
from pathlib import Path

DIRECTIVE_PATH = Path(__file__).resolve().parent / "docs" / "DIRECTIVE.md"

GOLD = """\
## Gold (do not change)

- **Target:** 10 TikTok email-signup accounts saved in `users.txt` (`email:password` per line).
- **Tool path:** `tools/tiktok-account-creator` only (not scratch copies).
- **Mode:** research-only — `--ack-research-only` / `TIKTOK_RESEARCH_ACK=1`.
- **Stop condition:** `users.txt` has 10 valid lines OR user cancels.
"""

DEFAULT_PLAN = """\
## Current plan (dynamic)

1. Skills: `gws-gmail`, `selenium-automation`, `imap-smtp-email` (skills.sh installs).
2. Browser: `chrome_uc` + Pixel 5 UA; fresh profile each attempt.
3. OTP: IMAP primary → `bin/gmail-tiktok-code` (gws) fallback.
4. Batch: `python research_loop.py --ack-research-only --target 10`.
5. On `otp_timeout`: cooldown 30m + verify `from:account.tiktok` in Gmail before retry.
"""


def _count_users() -> int:
    users = DIRECTIVE_PATH.parent.parent / "users.txt"
    if not users.exists():
        return 0
    return sum(1 for line in users.read_text(encoding="utf-8").splitlines() if ":" in line.strip())


def append_issue_log(status: str, detail: str, action_taken: str) -> None:
    """Append to issue log section and refresh progress header."""
    now = datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M UTC")
    successes = _count_users()

    if DIRECTIVE_PATH.exists():
        body = DIRECTIVE_PATH.read_text(encoding="utf-8")
    else:
        body = f"# TikTok signup — living directive\n\n{GOLD}\n\n{DEFAULT_PLAN}\n\n## Issue → solution log\n"

    entry = (
        f"\n### {now} — `{status}`\n"
        f"- **Observed:** {detail[:400]}\n"
        f"- **Action:** {action_taken[:400]}\n"
    )

    marker = "## Issue → solution log"
    if marker in body:
        body = body.rstrip() + entry
    else:
        body = body.rstrip() + f"\n\n{marker}\n" + entry

    progress = (
        f"## Progress\n\n"
        f"- **Accounts:** {successes} / 10\n"
        f"- **Last update:** {now}\n"
        f"- **Last status:** `{status}`\n"
    )
    if "## Progress" in body:
        import re

        body = re.sub(r"## Progress\n\n.*?(?=\n## |\Z)", progress + "\n", body, count=1, flags=re.S)
    else:
        body = body.replace(GOLD, GOLD + "\n\n" + progress)

    DIRECTIVE_PATH.parent.mkdir(parents=True, exist_ok=True)
    DIRECTIVE_PATH.write_text(body, encoding="utf-8")


def ensure_bootstrap() -> None:
    if DIRECTIVE_PATH.exists():
        return
    DIRECTIVE_PATH.parent.mkdir(parents=True, exist_ok=True)
    DIRECTIVE_PATH.write_text(
        f"# TikTok signup — living directive\n\n{GOLD}\n\n## Progress\n\n- **Accounts:** 0 / 10\n\n{DEFAULT_PLAN}\n\n## Issue → solution log\n",
        encoding="utf-8",
    )
