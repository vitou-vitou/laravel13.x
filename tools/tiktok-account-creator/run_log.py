"""CSV audit trail for signup attempts (compliance / incident review)."""

from __future__ import annotations

import csv
from datetime import datetime, timezone
from pathlib import Path

RUN_LOG_PATH = Path(__file__).resolve().parent / "runs.csv"
FIELDNAMES = (
    "timestamp_utc",
    "email_alias",
    "status",
    "detail",
    "url",
    "browser",
)


def _ensure_header(path: Path = RUN_LOG_PATH) -> None:
    if path.exists():
        return
    with path.open("w", newline="", encoding="utf-8") as fh:
        csv.DictWriter(fh, fieldnames=FIELDNAMES).writeheader()


def append_run(
    *,
    email_alias: str,
    status: str,
    detail: str = "",
    url: str = "",
    browser: str = "firefox",
    path: Path = RUN_LOG_PATH,
) -> None:
    _ensure_header(path)
    row = {
        "timestamp_utc": datetime.now(timezone.utc).isoformat(),
        "email_alias": email_alias,
        "status": status,
        "detail": detail[:500],
        "url": url[:300],
        "browser": browser,
    }
    with path.open("a", newline="", encoding="utf-8") as fh:
        csv.DictWriter(fh, fieldnames=FIELDNAMES).writerow(row)


def count_successes_today(path: Path = RUN_LOG_PATH) -> int:
    if not path.exists():
        return 0
    today = datetime.now(timezone.utc).date().isoformat()
    count = 0
    with path.open(newline="", encoding="utf-8") as fh:
        for row in csv.DictReader(fh):
            if not row.get("timestamp_utc", "").startswith(today):
                continue
            if row.get("status") == "success":
                count += 1
    return count
