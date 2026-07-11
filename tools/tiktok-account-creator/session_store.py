"""Persist browser cookies per account for login → post handoff."""

from __future__ import annotations

import hashlib
import json
from pathlib import Path

SESSIONS_DIR = Path(__file__).resolve().parent / "sessions"


def _session_path(email: str) -> Path:
    digest = hashlib.sha256(email.strip().lower().encode()).hexdigest()[:16]
    safe = email.split("@", 1)[0].replace("+", "_")[:24]
    return SESSIONS_DIR / f"{safe}-{digest}.json"


def save_cookies(driver, email: str) -> Path:
    SESSIONS_DIR.mkdir(parents=True, exist_ok=True)
    path = _session_path(email)
    cookies = driver.get_cookies()
    path.write_text(json.dumps(cookies, indent=2), encoding="utf-8")
    return path


def load_cookies(driver, email: str, base_url: str = "https://www.tiktok.com") -> bool:
    path = _session_path(email)
    if not path.is_file():
        return False
    try:
        cookies = json.loads(path.read_text(encoding="utf-8"))
    except (json.JSONDecodeError, OSError):
        return False
    driver.get(base_url)
    for cookie in cookies:
        try:
            driver.add_cookie(cookie)
        except Exception:
            continue
    driver.refresh()
    return True


def has_session(email: str) -> bool:
    return _session_path(email).is_file()
