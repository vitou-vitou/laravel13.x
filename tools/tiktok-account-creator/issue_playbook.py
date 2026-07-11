"""Map signup failure statuses to remediation steps (research loop)."""

from __future__ import annotations

PLAYBOOK: dict[str, list[str]] = {
    "rate_limited": [
        "Wait 60+ minutes before retry; TikTok locks IP + fingerprint.",
        "Switch to a fresh residential or LTE mobile proxy (not datacenter VPN).",
        "Use browser=chrome_uc or chrome_mobile with a new profile per attempt.",
        "Lower volume: respect maxAccountsPerDay in settings.json.",
    ],
    "otp_timeout": [
        "Run: python diagnose.py probe --limit 10 — confirm TikTok mail arrives.",
        "Check Spam/All Mail; filter must match account.tiktok sender.",
        "Verify Send code actually triggered (resend timer or OTP field).",
        "If zero TikTok mail: rate limit likely — wait and change IP/proxy.",
    ],
    "captcha": [
        "Manual solve required — automation cannot pass TikTok captcha reliably.",
        "Retry with chrome_uc + mobile proxy matching target geo.",
        "Reduce automation speed (human_pause already applied).",
    ],
    "incomplete": [
        "Screenshot saved — confirm URL left /signup/phone-or-email/email.",
        "Check for username step or Skip button after OTP.",
        "Re-run dry-run: python bot.py --dry-run",
    ],
    "policy_blocked": [
        "Pass --ack-research-only or set TIKTOK_RESEARCH_ACK=1.",
        "Read: python diagnose.py policy",
    ],
    "error": [
        "Check debug-*.png screenshots in tool directory.",
        "Verify geckoPath/chromePath in settings.json.",
        "Run: python diagnose.py login",
    ],
    "send_code_failed": [
        "Send code button stayed disabled — birthday/email/password incomplete.",
        "Run dry-run and verify all locators probe true.",
        "Wait for form validation; increase SEND_CODE_WAIT_SECONDS if needed.",
    ],
}


def hints_for(status: str) -> list[str]:
    return list(PLAYBOOK.get(status, PLAYBOOK["error"]))


def format_hints(status: str) -> str:
    lines = hints_for(status)
    return "\n".join(f"  -> {line}" for line in lines)
