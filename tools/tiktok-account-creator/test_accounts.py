"""Tests for accounts.py."""

from accounts import Account, _parse_lines, load_accounts


def test_parse_users_line():
    text = "user+1@gmail.com:secret123\n# comment\nbadline\n"
    accs = _parse_lines(text)
    assert len(accs) == 1
    assert accs[0] == Account("user+1@gmail.com", "secret123")
