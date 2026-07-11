"""Tests for policy preflight and run logging."""

from __future__ import annotations

import csv
from pathlib import Path

import policy as policy_mod
import run_log as run_log_mod


def test_preflight_requires_ack():
    check = policy_mod.preflight(dry_run=False, argv=[])
    assert not check.ok
    assert check.blocked_reason == "missing_research_ack"


def test_preflight_dry_run_skips_ack():
    check = policy_mod.preflight(dry_run=True, argv=[])
    assert check.ok


def test_preflight_ack_flag():
    check = policy_mod.preflight(
        dry_run=False, argv=["bot.py", policy_mod.RESEARCH_ACK_FLAG]
    )
    assert check.ok


def test_daily_cap(tmp_path: Path):
    log_path = tmp_path / "runs.csv"
    run_log_mod.append_run(
        email_alias="a@b.com",
        status="success",
        path=log_path,
    )
    check = policy_mod.preflight(
        dry_run=False,
        argv=[policy_mod.RESEARCH_ACK_FLAG],
        max_accounts_per_day=1,
        accounts_created_today=run_log_mod.count_successes_today(log_path),
    )
    assert not check.ok
    assert check.blocked_reason == "daily_cap"


def test_append_run_creates_csv(tmp_path: Path):
    log_path = tmp_path / "runs.csv"
    run_log_mod.append_run(
        email_alias="user+1@gmail.com",
        status="started",
        detail="test",
        path=log_path,
    )
    with log_path.open(encoding="utf-8") as fh:
        rows = list(csv.DictReader(fh))
    assert len(rows) == 1
    assert rows[0]["status"] == "started"
    assert rows[0]["email_alias"] == "user+1@gmail.com"
