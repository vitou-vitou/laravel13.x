"""
Runtime strategy rotation — browser + proxy per attempt based on last failure.

Goal unchanged: reach target account count. Plan: rotate surface when stuck.
"""

from __future__ import annotations

import json
from pathlib import Path

import proxy_auto

STATE_PATH = Path(__file__).resolve().parent / "goal_state.json"

DEFAULT_BROWSERS = ("firefox", "chrome_mobile", "chrome_uc")

RUNTIME: dict = {}


def load_state() -> dict:
    if not STATE_PATH.exists():
        return {"browser_index": 0, "proxy_index": 0, "last_result": ""}
    try:
        return json.loads(STATE_PATH.read_text(encoding="utf-8"))
    except (json.JSONDecodeError, OSError):
        return {"browser_index": 0, "proxy_index": 0, "last_result": ""}


def save_state(state: dict) -> None:
    STATE_PATH.write_text(json.dumps(state, indent=2), encoding="utf-8")


def browser_chain(settings: dict) -> tuple[str, ...]:
    custom = settings.get("browserStrategies")
    if isinstance(custom, list) and custom:
        return tuple(str(b).strip().lower() for b in custom if b)
    return DEFAULT_BROWSERS


def proxy_list(settings: dict) -> list[dict]:
    items = settings.get("proxyList")
    if isinstance(items, list):
        manual = [p for p in items if isinstance(p, dict) and p.get("host") and p.get("port")]
        if manual:
            return manual
    single = settings.get("proxy")
    if isinstance(single, dict) and single.get("host") and single.get("port"):
        return [single]
    if settings.get("autoProxy", True):
        chain = settings.get("_autoProxyChain")
        if not isinstance(chain, list) or not chain:
            chain = proxy_auto.build_auto_chain()
            settings["_autoProxyChain"] = chain
        return [p for p in chain if isinstance(p, dict)]
    return []


def advance_after_failure(settings: dict, result: str) -> dict:
    """Rotate browser on hard failures; rotate proxy on network/OTP issues."""
    state = load_state()
    state["last_result"] = result
    browsers = browser_chain(settings)
    proxies = proxy_list(settings)

    rotate_browser = result in (
        "error",
        "otp_timeout",
        "rate_limited",
        "captcha",
        "send_code_failed",
        "incomplete",
    )
    rotate_proxy = result in ("otp_timeout", "rate_limited", "error")

    if rotate_browser and browsers:
        state["browser_index"] = (int(state.get("browser_index", 0)) + 1) % len(browsers)
    if rotate_proxy:
        if result in ("otp_timeout", "rate_limited"):
            settings["_autoProxyChain"] = proxy_auto.build_auto_chain(refresh_pool=True)
        proxies = proxy_list(settings)
        if proxies:
            state["proxy_index"] = (int(state.get("proxy_index", 0)) + 1) % len(proxies)
            chosen = proxies[state["proxy_index"] % len(proxies)]
            if chosen.get("source") == "tor_local":
                proxy_auto.tor_new_identity()

    save_state(state)
    return state


def apply_runtime(settings: dict) -> dict:
    """Merge rotated browser/proxy into settings for the next attempt."""
    state = load_state()
    merged = dict(settings)
    browsers = browser_chain(settings)
    proxies = proxy_list(settings)

    if browsers:
        idx = int(state.get("browser_index", 0)) % len(browsers)
        merged["browser"] = browsers[idx]

    if proxies:
        pidx = int(state.get("proxy_index", 0)) % len(proxies)
        chosen = dict(proxies[pidx])
        if chosen.get("host") and chosen.get("port"):
            merged["proxy"] = chosen
        else:
            merged.pop("proxy", None)

    RUNTIME.clear()
    RUNTIME.update(
        {
            "browser": merged.get("browser"),
            "proxy": merged.get("proxy"),
        }
    )
    return merged


def current_label(settings: dict) -> str:
    merged = apply_runtime(settings)
    proxy = merged.get("proxy") or {}
    host = proxy.get("host") or proxy.get("source") or "direct"
    return f"{merged.get('browser', '?')} @ {host}"
