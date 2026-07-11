"""Tests for session_store.py."""

from session_store import _session_path, has_session


def test_session_path_stable():
    p1 = _session_path("user+1@gmail.com")
    p2 = _session_path("user+1@gmail.com")
    assert p1 == p2
    assert p1.name.endswith(".json")


def test_has_session_false():
    assert has_session("nonexistent-account@example.com") is False
