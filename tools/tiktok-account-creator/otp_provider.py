"""Unified OTP fetch: IMAP primary, optional gws CLI fallback (gws-gmail-triage skill)."""

from __future__ import annotations

import re
import subprocess
from pathlib import Path

import gmailReader as gr

REPO_ROOT = Path(__file__).resolve().parents[2]
GWS_SCRIPT = REPO_ROOT / "bin" / "gmail-tiktok-code"
GWS_QUERY = 'from:account.tiktok OR from:tiktok newer_than:1h (verification OR code OR verify)'


def _plausible(code: str) -> bool:
    return gr._is_plausible_otp(code)


def fetch_via_imap(recipient: str | None, since_epoch: float | None) -> str:
    return gr.getmail(recipient=recipient, since_epoch=since_epoch)


def fetch_via_gws(max_wait: int = 90) -> str:
    """Use repo bin/gmail-tiktok-code when gws is authenticated."""
    if not GWS_SCRIPT.is_file():
        return ""
    env = {**__import__("os").environ, "MAX_WAIT": str(max_wait), "GMAIL_TIKTOK_QUERY": GWS_QUERY}
    try:
        proc = subprocess.run(
            ["bash", str(GWS_SCRIPT), "--once"],
            capture_output=True,
            text=True,
            timeout=max_wait + 30,
            cwd=str(REPO_ROOT),
            env=env,
        )
    except (subprocess.TimeoutExpired, FileNotFoundError, OSError):
        return ""
    if proc.returncode != 0:
        return ""
    code = (proc.stdout or "").strip()
    if _plausible(code):
        return code
    match = re.search(r"\b(\d{6})\b", code)
    if match and _plausible(match.group(1)):
        return match.group(1)
    return ""


def fetch_otp(
    *,
    recipient: str | None,
    since_epoch: float | None,
    use_gws_fallback: bool = True,
) -> str:
    code = fetch_via_imap(recipient, since_epoch)
    if code:
        return code
    if use_gws_fallback:
        return fetch_via_gws()
    return ""
