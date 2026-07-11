"""Load accounts from users.txt (email:password per line)."""

from __future__ import annotations

from dataclasses import dataclass
from pathlib import Path

USERS_PATH = Path(__file__).resolve().parent / "users.txt"
SCRATCH_USERS = (
    Path(__file__).resolve().parents[1]
    / "_scratch"
    / "tiktok-account-audit"
    / "Andromeda606-TikTok-AccountCreator"
    / "users.txt"
)


@dataclass(frozen=True)
class Account:
    email: str
    password: str

    @property
    def local_part(self) -> str:
        return self.email.split("@", 1)[0]


def _parse_lines(text: str) -> list[Account]:
    out: list[Account] = []
    for line in text.splitlines():
        line = line.strip()
        if not line or line.startswith("#") or ":" not in line:
            continue
        email, password = line.split(":", 1)
        email, password = email.strip(), password.strip()
        if email and password:
            out.append(Account(email=email, password=password))
    return out


def load_accounts(*, include_scratch: bool = True) -> list[Account]:
    accounts: list[Account] = []
    seen: set[str] = set()
    for path in (USERS_PATH, SCRATCH_USERS if include_scratch else None):
        if path is None or not path.is_file():
            continue
        for acc in _parse_lines(path.read_text(encoding="utf-8")):
            if acc.email not in seen:
                seen.add(acc.email)
                accounts.append(acc)
    return accounts


def get_latest_local_account() -> Account | None:
    """Last account appended to main users.txt (post-signup)."""
    if not USERS_PATH.is_file():
        return None
    accs = _parse_lines(USERS_PATH.read_text(encoding="utf-8"))
    return accs[-1] if accs else None


def get_account(
    email: str | None = None,
    index: int = 0,
    *,
    include_scratch: bool = True,
) -> Account | None:
    accounts = load_accounts(include_scratch=include_scratch)
    if not accounts:
        return None
    if email:
        needle = email.strip().lower()
        for acc in accounts:
            if acc.email.lower() == needle:
                return acc
        return None
    if 0 <= index < len(accounts):
        return accounts[index]
    return None
