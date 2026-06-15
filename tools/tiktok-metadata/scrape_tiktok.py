#!/usr/bin/env python3
"""
Internal TikTok metadata/archive CLI for authorized creator-commission ops.

Requires creator consent. Public content only unless --cookies is supplied by the creator.
"""

from __future__ import annotations

import argparse
import json
import logging
import re
import subprocess
import sys
import time
from pathlib import Path
from typing import Any, Iterator

logger = logging.getLogger(__name__)

RETRYABLE_PATTERN = re.compile(r"(HTTP Error 429|HTTP Error 5\d{2}|429|Too Many Requests)", re.I)
USERNAME_PATTERN = re.compile(r"^[A-Za-z0-9._]+$")


def normalize_username(username: str) -> str:
    return username.lstrip("@").strip()


def profile_url(username: str) -> str:
    return f"https://www.tiktok.com/@{normalize_username(username)}"


def normalize_metadata(raw: dict[str, Any]) -> dict[str, Any]:
    upload = str(raw.get("upload_date") or "")
    posted = ""
    if len(upload) == 8 and upload.isdigit():
        posted = f"{upload[0:4]}-{upload[4:6]}-{upload[6:8]}"

    availability = raw.get("availability") or raw.get("_type")
    status = "ok"
    if raw.get("is_private") or availability == "private":
        status = "skipped_private"
    elif raw.get("was_live") is False and raw.get("id") is None:
        status = "error"

    return {
        "video_id": raw.get("id"),
        "caption": raw.get("description") or raw.get("title") or "",
        "views": raw.get("view_count"),
        "likes": raw.get("like_count"),
        "shares": raw.get("repost_count"),
        "posted_date": posted,
        "video_url": raw.get("webpage_url") or raw.get("url") or "",
        "music_title": raw.get("track") or raw.get("artist") or "",
        "status": status,
    }


def filter_since_date(rows: list[dict[str, Any]], since_date: str | None) -> list[dict[str, Any]]:
    if not since_date:
        return rows
    return [row for row in rows if row.get("posted_date") and row["posted_date"] >= since_date]


def apply_limit(rows: list[dict[str, Any]], limit: int) -> list[dict[str, Any]]:
    if limit <= 0:
        return rows
    return rows[:limit]


def mp4_output_path(base_dir: Path, username: str, posted_date: str, video_id: str) -> Path:
    folder_date = posted_date or "unknown-date"
    return base_dir / normalize_username(username) / folder_date / f"{video_id}.mp4"


def append_jsonl(path: Path, record: dict[str, Any]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("a", encoding="utf-8") as handle:
        handle.write(json.dumps(record, ensure_ascii=False) + "\n")


def load_logged_video_ids(jsonl_path: Path) -> set[str]:
    if not jsonl_path.is_file():
        return set()
    ids: set[str] = set()
    for line in jsonl_path.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line:
            continue
        try:
            row = json.loads(line)
        except json.JSONDecodeError:
            continue
        video_id = row.get("video_id")
        if video_id:
            ids.add(str(video_id))
    return ids


def parse_json_lines(stdout: str) -> Iterator[dict[str, Any]]:
    """Parse yt-dlp stdout which may be NDJSON or pretty-printed JSON objects."""
    decoder = json.JSONDecoder()
    idx = 0
    text = stdout.strip()
    while idx < len(text):
        while idx < len(text) and text[idx].isspace():
            idx += 1
        if idx >= len(text):
            break
        try:
            obj, end = decoder.raw_decode(text, idx)
        except json.JSONDecodeError:
            next_line = text.find("\n", idx)
            if next_line == -1:
                break
            idx = next_line + 1
            continue
        if isinstance(obj, dict):
            yield obj
        idx = end


def run_ytdlp(
    args: list[str],
    *,
    max_retries: int = 5,
    base_delay: float = 2.0,
) -> subprocess.CompletedProcess[str]:
    last_result: subprocess.CompletedProcess[str] | None = None
    for attempt in range(max_retries + 1):
        result = subprocess.run(
            args,
            capture_output=True,
            text=True,
            check=False,
        )
        last_result = result
        combined = (result.stdout or "") + (result.stderr or "")
        if result.returncode == 0 or not RETRYABLE_PATTERN.search(combined):
            return result
        if attempt == max_retries:
            break
        delay = base_delay * (2**attempt)
        logger.warning("Retryable yt-dlp error (attempt %s/%s); sleeping %.1fs", attempt + 1, max_retries, delay)
        time.sleep(delay)
    assert last_result is not None
    return last_result


def fetch_playlist_entries(username: str, cookies: Path | None) -> list[dict[str, Any]]:
    cmd = [
        sys.executable,
        "-m",
        "yt_dlp",
        "--ignore-errors",
        "--no-warnings",
        "--dump-json",
        "--sleep-interval",
        "1",
        "--max-sleep-interval",
        "3",
    ]
    if cookies:
        cmd.extend(["--cookies", str(cookies)])
    cmd.append(profile_url(username))

    result = run_ytdlp(cmd)
    if result.returncode not in (0, 1) and not result.stdout:
        raise RuntimeError(result.stderr.strip() or f"yt-dlp exited {result.returncode}")

    entries: list[dict[str, Any]] = []
    for raw in parse_json_lines(result.stdout):
        meta = normalize_metadata(raw)
        if not meta.get("video_id"):
            logger.info("Skipping entry without video_id: %s", meta.get("video_url") or raw.get("title"))
            continue
        if meta["status"] == "skipped_private":
            logger.info("Skipping private/unavailable: %s", meta["video_id"])
            entries.append(meta)
            continue
        entries.append(meta)
    return entries


def download_mp4(
    video_url: str,
    output_path: Path,
    cookies: Path | None,
) -> bool:
    if output_path.is_file() and output_path.stat().st_size > 0:
        logger.info("Skip existing %s", output_path)
        return True

    output_path.parent.mkdir(parents=True, exist_ok=True)
    template = str(output_path.with_suffix("")) + ".%(ext)s"
    cmd = [
        sys.executable,
        "-m",
        "yt_dlp",
        "--no-warnings",
        "--sleep-interval",
        "1",
        "--max-sleep-interval",
        "3",
        "-f",
        "best[ext=mp4]/best",
        "-o",
        template,
    ]
    if cookies:
        cmd.extend(["--cookies", str(cookies)])
    cmd.append(video_url)

    result = run_ytdlp(cmd)
    if result.returncode != 0:
        logger.error("Download failed for %s: %s", video_url, result.stderr.strip())
        return False

    if output_path.is_file():
        return True

    for candidate in output_path.parent.glob(f"{output_path.stem}.*"):
        if candidate.suffix.lower() in {".mp4", ".mov", ".webm"}:
            if candidate != output_path:
                candidate.rename(output_path)
            return True
    return False


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        description="Authorized TikTok profile metadata/archive tool for creator-commission ops.",
    )
    parser.add_argument("--username", required=True, help="TikTok handle (with or without @)")
    parser.add_argument("--limit", type=int, default=0, help="Max videos after filters (0 = no limit)")
    parser.add_argument("--since-date", dest="since_date", help="Only videos on/after YYYY-MM-DD")
    parser.add_argument(
        "--output-dir",
        default="./downloads",
        help="Base download directory (default: ./downloads)",
    )
    parser.add_argument(
        "--metadata-only",
        action="store_true",
        help="Write metadata.jsonl only; do not download MP4 files",
    )
    parser.add_argument(
        "--cookies",
        type=Path,
        help="Netscape cookies file from creator (optional)",
    )
    parser.add_argument(
        "--skip-logged",
        action="store_true",
        default=True,
        help="Skip videos already present in metadata.jsonl (default: true)",
    )
    parser.add_argument(
        "--no-skip-logged",
        dest="skip_logged",
        action="store_false",
        help="Re-process videos even if already logged",
    )
    parser.add_argument("-v", "--verbose", action="store_true")
    return parser


def main(argv: list[str] | None = None) -> int:
    parser = build_parser()
    args = parser.parse_args(argv)

    logging.basicConfig(
        level=logging.DEBUG if args.verbose else logging.INFO,
        format="%(levelname)s: %(message)s",
    )

    username = normalize_username(args.username)
    if not USERNAME_PATTERN.match(username):
        logger.error("Invalid username: %s", args.username)
        return 2

    if args.cookies and not args.cookies.is_file():
        logger.error("Cookies file not found: %s", args.cookies)
        return 2

    base_dir = Path(args.output_dir)
    jsonl_path = base_dir / username / "metadata.jsonl"
    logged_ids = load_logged_video_ids(jsonl_path) if args.skip_logged else set()

    logger.info("Fetching playlist for @%s", username)
    try:
        entries = fetch_playlist_entries(username, args.cookies)
    except RuntimeError as exc:
        logger.error("%s", exc)
        return 1

    entries = filter_since_date(entries, args.since_date)
    entries = apply_limit(entries, args.limit)
    total = len(entries)
    logger.info("Processing %s videos", total)

    processed = 0
    for index, meta in enumerate(entries, start=1):
        video_id = str(meta["video_id"])
        if args.skip_logged and video_id in logged_ids:
            logger.info("[%s/%s] Skip logged %s", index, total, video_id)
            continue

        record = dict(meta)
        record["username"] = username

        if meta["status"] != "ok":
            logger.info("[%s/%s] %s (%s)", index, total, video_id, meta["status"])
            append_jsonl(jsonl_path, record)
            processed += 1
            continue

        video_url = record.get("video_url") or f"{profile_url(username)}/video/{video_id}"
        mp4_path = mp4_output_path(base_dir, username, record.get("posted_date") or "", video_id)

        if not args.metadata_only:
            ok = download_mp4(video_url, mp4_path, args.cookies)
            record["mp4_path"] = str(mp4_path)
            if not ok:
                record["status"] = "error"
                logger.warning("[%s/%s] Download failed %s", index, total, video_id)
            elif mp4_path.is_file():
                record["status"] = "downloaded" if record["status"] == "ok" else record["status"]

        append_jsonl(jsonl_path, record)
        logged_ids.add(video_id)
        processed += 1
        logger.info("[%s/%s] Logged %s", index, total, video_id)

    logger.info("Done. %s/%s videos written to %s", processed, total, jsonl_path)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
