"""Tests for proxy_auto.build_auto_chain ordering."""

from __future__ import annotations

import proxy_auto


def test_build_auto_chain_direct_first(monkeypatch):
    monkeypatch.setattr(proxy_auto, "tor_endpoint", lambda: None)
    monkeypatch.setattr(proxy_auto, "load_cached_pool", lambda: [])
    monkeypatch.setattr(proxy_auto, "refresh_free_pool", lambda max_valid=6: [])

    chain = proxy_auto.build_auto_chain(refresh_pool=False)
    assert chain
    assert chain[0].get("source") == "direct"
    assert not chain[0].get("host")
