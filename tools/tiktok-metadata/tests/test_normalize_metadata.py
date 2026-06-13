import json
from pathlib import Path

from scrape_tiktok import (
    apply_limit,
    filter_since_date,
    mp4_output_path,
    normalize_metadata,
    parse_json_lines,
)

FIXTURES = Path(__file__).parent / "fixtures"


def test_normalize_metadata_maps_core_fields():
    raw = {
        "id": "7123456789012345678",
        "description": "Test caption",
        "view_count": 12000,
        "like_count": 800,
        "repost_count": 40,
        "upload_date": "20260601",
        "webpage_url": "https://www.tiktok.com/@example/video/7123456789012345678",
        "track": "original sound - example",
    }
    out = normalize_metadata(raw)
    assert out["video_id"] == "7123456789012345678"
    assert out["caption"] == "Test caption"
    assert out["views"] == 12000
    assert out["likes"] == 800
    assert out["shares"] == 40
    assert out["posted_date"] == "2026-06-01"
    assert out["video_url"] == raw["webpage_url"]
    assert out["music_title"] == "original sound - example"
    assert out["status"] == "ok"


def test_normalize_metadata_from_fixture_file():
    raw = json.loads((FIXTURES / "sample_video.json").read_text(encoding="utf-8"))
    out = normalize_metadata(raw)
    assert out["video_id"] == raw["id"]
    assert out["caption"] == raw["description"]


def test_normalize_metadata_falls_back_to_title():
    out = normalize_metadata({"id": "1", "title": "Only title"})
    assert out["caption"] == "Only title"


def test_filter_since_date():
    rows = [
        {"posted_date": "2026-06-01"},
        {"posted_date": "2026-05-01"},
        {"posted_date": ""},
    ]
    filtered = filter_since_date(rows, "2026-05-15")
    assert len(filtered) == 1
    assert filtered[0]["posted_date"] == "2026-06-01"


def test_apply_limit_zero_means_no_limit():
    rows = [{"video_id": str(i)} for i in range(5)]
    assert len(apply_limit(rows, 0)) == 5


def test_apply_limit():
    rows = [{"video_id": str(i)} for i in range(5)]
    assert len(apply_limit(rows, 2)) == 2


def test_mp4_output_path():
    base = Path("/downloads")
    path = mp4_output_path(base, "creator", "2026-06-01", "999")
    assert path == base / "creator" / "2026-06-01" / "999.mp4"


def test_parse_json_lines_ndjson():
    stdout = '{"id":"1","title":"a"}\n{"id":"2","title":"b"}\n'
    rows = list(parse_json_lines(stdout))
    assert len(rows) == 2
    assert rows[0]["id"] == "1"


def test_normalize_metadata_private():
    out = normalize_metadata({"id": "1", "is_private": True})
    assert out["status"] == "skipped_private"
