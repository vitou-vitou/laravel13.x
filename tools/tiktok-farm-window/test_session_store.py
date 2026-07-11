from __future__ import annotations

from session_store import FarmAccount, load_accounts, save_accounts, upsert_account


def test_upsert_and_load(tmp_path, monkeypatch):
    import session_store as mod

    monkeypatch.setattr(mod, "ACCOUNTS_FILE", tmp_path / "accounts.json")
    upsert_account("a@b.com", "secret1")
    upsert_account("c@d.com", "secret2")
    rows = load_accounts()
    assert len(rows) == 2
    assert rows[0].email == "a@b.com"
    upsert_account("a@b.com", "newpass")
    rows = load_accounts()
    assert rows[0].password == "newpass"


def test_save_roundtrip(tmp_path, monkeypatch):
    import session_store as mod

    path = tmp_path / "accounts.json"
    monkeypatch.setattr(mod, "ACCOUNTS_FILE", path)
    acc = FarmAccount(email="x@y.com", password="p", created_at="2026-01-01T00:00:00Z")
    save_accounts([acc])
    loaded = load_accounts()
    assert loaded[0].email == "x@y.com"
