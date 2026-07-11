"""Account registry for farm-window sessions."""

from __future__ import annotations

import json
from dataclasses import asdict, dataclass
from datetime import datetime, timezone
from pathlib import Path

from paths import ACCOUNTS_FILE, USERS_PATH


@dataclass
class FarmAccount:
    email: str
    password: str
    created_at: str
    last_login: str | None = None
    last_post: str | None = None
    posts: int = 0


def _now() -> str:
    return datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")


def load_accounts() -> list[FarmAccount]:
    if not ACCOUNTS_FILE.is_file():
        return _migrate_from_users_txt()
    try:
        raw = json.loads(ACCOUNTS_FILE.read_text(encoding="utf-8"))
    except (json.JSONDecodeError, OSError):
        return []
    items = raw if isinstance(raw, list) else raw.get("accounts", [])
    out: list[FarmAccount] = []
    for item in items:
        if isinstance(item, dict) and item.get("email") and item.get("password"):
            out.append(
                FarmAccount(
                    email=item["email"],
                    password=item["password"],
                    created_at=item.get("created_at") or _now(),
                    last_login=item.get("last_login"),
                    last_post=item.get("last_post"),
                    posts=int(item.get("posts") or 0),
                )
            )
    return out


def save_accounts(accounts: list[FarmAccount]) -> None:
    ACCOUNTS_FILE.parent.mkdir(parents=True, exist_ok=True)
    ACCOUNTS_FILE.write_text(
        json.dumps([asdict(a) for a in accounts], indent=2),
        encoding="utf-8",
    )


def _migrate_from_users_txt() -> list[FarmAccount]:
    if not USERS_PATH.is_file():
        return []
    accounts: list[FarmAccount] = []
    for line in USERS_PATH.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if ":" not in line:
            continue
        email, password = line.split(":", 1)
        accounts.append(FarmAccount(email=email, password=password, created_at=_now()))
    if accounts:
        save_accounts(accounts)
    return accounts


def upsert_account(email: str, password: str) -> FarmAccount:
    accounts = load_accounts()
    for acc in accounts:
        if acc.email == email:
            acc.password = password
            save_accounts(accounts)
            return acc
    acc = FarmAccount(email=email, password=password, created_at=_now())
    accounts.append(acc)
    save_accounts(accounts)
    return acc


def mark_login(email: str) -> None:
    accounts = load_accounts()
    for acc in accounts:
        if acc.email == email:
            acc.last_login = _now()
            save_accounts(accounts)
            return


def mark_post(email: str) -> None:
    accounts = load_accounts()
    for acc in accounts:
        if acc.email == email:
            acc.last_post = _now()
            acc.posts += 1
            save_accounts(accounts)
            return


def latest_account() -> FarmAccount | None:
    accounts = load_accounts()
    return accounts[-1] if accounts else None
