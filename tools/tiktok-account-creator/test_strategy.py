"""Tests for strategy rotation."""

from __future__ import annotations

import strategy as strategy_mod


def test_advance_rotates_browser(tmp_path, monkeypatch):
    state_file = tmp_path / "goal_state.json"
    monkeypatch.setattr(strategy_mod, "STATE_PATH", state_file)
    monkeypatch.setattr(
        strategy_mod.proxy_auto,
        "build_auto_chain",
        lambda refresh_pool=False: [{"source": "direct", "host": "", "port": 0}],
    )
    settings = {"browserStrategies": ["chrome_uc", "firefox"]}
    strategy_mod.save_state({"browser_index": 0, "proxy_index": 0})
    strategy_mod.advance_after_failure(settings, "otp_timeout")
    state = strategy_mod.load_state()
    assert state["browser_index"] == 1


def test_apply_runtime_uses_chain():
    settings = {"browser": "firefox", "browserStrategies": ["chrome_uc", "firefox"]}
    strategy_mod.RUNTIME.clear()
    strategy_mod.save_state({"browser_index": 0, "proxy_index": 0})
    merged = strategy_mod.apply_runtime(settings)
    assert merged["browser"] == "chrome_uc"
