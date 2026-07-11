"""Email signup via visible mobile-emulated browser (delegates to account-creator)."""

from __future__ import annotations

import random
import sys
from pathlib import Path

_TOOL = Path(__file__).resolve().parent.parent
_CREATOR = _TOOL.parent / "tiktok-account-creator"
for p in (_TOOL, _CREATOR):
    if str(p) not in sys.path:
        sys.path.insert(0, str(p))

import bot as creator_bot  # noqa: E402
import run_log as run_log_mod  # noqa: E402
import strategy as strategy_mod  # noqa: E402
from session_store import upsert_account  # noqa: E402
from window_browser import create_window_browser  # noqa: E402


def _alias_email(settings: dict) -> str:
    email_user = settings["email"]
    email_end = settings["eMailEnd"]
    return f"{email_user}+{random.randint(1, 99999)}@{email_end}"


def run_signup(settings: dict, *, keep_open: bool = False, use_strategy: bool = True) -> tuple[str, str, str]:
    """Returns (status, email, password)."""
    base = strategy_mod.apply_runtime(settings) if use_strategy else dict(settings)
    merged = dict(base)
    if not use_strategy:
        merged.pop("proxy", None)
        merged["browser"] = settings.get("browser") or "firefox"
    alias = _alias_email(merged)
    password = merged["password"]

    run_log_mod.append_run(
        email_alias=alias,
        status="started",
        detail="farm_window_signup",
        browser=merged.get("browser", "?"),
    )

    driver = None
    try:
        driver = create_window_browser(merged, email=alias, mobile=True)
        status = creator_bot.run_signup_flow(driver, alias, password)
        if status == "success":
            upsert_account(alias, password)
            creator_bot.successReg(alias, password)
        return status, alias, password
    except Exception as exc:
        run_log_mod.append_run(
            email_alias=alias,
            status="error",
            detail=str(exc)[:500],
            browser=merged.get("browser", "?"),
        )
        return f"error:{exc!s:.120}", alias, password
    finally:
        if driver is not None and not keep_open:
            try:
                driver.quit()
            except Exception:
                pass
