# TikTok Metadata CLI (internal ops)

Authorized **internal tool** for the [Creator Commission pilot](../../docs/creator-commission/README.md). Use only with **creator consent** and for **publicly accessible** content (or `--cookies` supplied by the creator).

This tool supports **metadata capture and backup** for the weekly publish log. It is **not** a substitute for clean, watermark-free masters used on YouTube Shorts.

## Legal / ToS

- Personal research and **licensed creator-commission operations** only.
- Respect TikTok rate limits; do not hammer profiles.
- **You** are responsible for copyright, platform Terms of Service, and how downloaded media is used.
- Not legal advice. See [agreement-outline.md](../../docs/creator-commission/agreement-outline.md).

## Install

```bash
cd tools/tiktok-metadata
pip install -r requirements.txt
```

Requires `yt-dlp` (installed via requirements).

## Output layout

```
./downloads/{username}/metadata.jsonl
./downloads/{username}/YYYY-MM-DD/{video_id}.mp4   # unless --metadata-only
```

Each line in `metadata.jsonl` is one JSON object:

```json
{
  "video_id": "7123456789012345678",
  "caption": "...",
  "views": 12000,
  "likes": 800,
  "shares": 40,
  "posted_date": "2026-06-01",
  "video_url": "https://www.tiktok.com/@handle/video/...",
  "music_title": "original sound - example",
  "status": "ok",
  "username": "handle"
}
```

Status values: `ok`, `downloaded`, `skipped_private`, `error`.

## Usage

```bash
# Metadata only (recommended for publish-log BUILD LIST step)
python scrape_tiktok.py --username creatorhandle --limit 10 --metadata-only

# Since date filter
python scrape_tiktok.py --username creatorhandle --since-date 2026-06-01 --metadata-only

# Download MP4 backups (authorized backup — not YT master)
python scrape_tiktok.py --username creatorhandle --limit 5

# With creator-provided cookies file
python scrape_tiktok.py --username creatorhandle --cookies /path/to/cookies.txt --metadata-only
```

## Behaviour

- **Rate limits:** `--sleep-interval` / `--max-sleep-interval` passed to yt-dlp
- **Retries:** exponential backoff on HTTP 429 / 5xx from yt-dlp
- **Resume:** skips MP4 if file exists; skips videos already in `metadata.jsonl` (use `--no-skip-logged` to re-run)
- **Progress:** logs `[N/total]` per video
- **Private/deleted:** logged with `skipped_private` or `error`; run continues

## Tests

```bash
pytest tests/ -v
```

## Related docs

- [Design spec](../../docs/superpowers/specs/2026-06-13-creator-commission-tiktok-first-design.md)
- [Weekly batch checklist](../../docs/creator-commission/weekly-batch-checklist.md) — BUILD LIST step
