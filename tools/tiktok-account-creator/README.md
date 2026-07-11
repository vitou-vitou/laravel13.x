# TikTok account creator (patched)

Fork of [Andromeda606/TikTok-AccountCreator](https://github.com/Andromeda606/TikTok-AccountCreator), updated for **2026 TikTok web signup** (email flow), **Selenium 4**, and **Gmail IMAP** OTP parsing.

**Core features:** signup, login (session cookies), post (video upload).

## Requirements

- Python 3.10+
- Mozilla Firefox + [geckodriver](https://github.com/mozilla/geckodriver/releases)
- Gmail account with [app password](https://myaccount.google.com/apppasswords) (IMAP enabled)

```bash
pip install -r requirements.txt
```

## Setup

1. Copy `settings.example.json` → `settings.json`.
2. Set `email`, `eMailEnd`, `gmailPass`, `geckoPath`, and `password` (TikTok password for new accounts).
3. Optional: copy `users.example.txt` → `users.txt` for login/post on existing accounts.
4. Review policy: `python diagnose.py policy`

## CLI (signup / login / post)

```bash
# List accounts (users.txt + scratch copy)
python cli.py accounts

# Signup — same as bot.py
python cli.py signup --ack-research-only
python cli.py signup --dry-run

# Login — saves cookies under sessions/
python cli.py login --index 0
python cli.py login --email 'you+alias@gmail.com'

# Post — requires .mp4 and logged-in session (re-login if needed)
python cli.py post --video assets/sample.mp4 --caption "Hello" --index 0

# Full pipeline: signup → login → optional post
python cli.py pipeline --signup --ack-research-only
python cli.py pipeline --signup --video assets/sample.mp4 --caption "Hello"
```

Legacy entry points still work:

```bash
python bot.py --ack-research-only
python bot.py --dry-run
```

Or set `TIKTOK_RESEARCH_ACK=1` in the environment.

## Output

Successful signups append to `users.txt`:

```text
user+alias@gmail.com:password
```

Login stores cookies in `sessions/` for post without re-entering credentials.

## Flow (signup)

1. Birthday (Month / Day / Year comboboxes)
2. Email (`+alias` Gmail addressing)
3. Password
4. Optional marketing consent
5. Send code → IMAP poll → 6-digit OTP → Next

## Flow (login)

1. Open email login URL
2. Email + password (or email OTP fallback via Gmail)
3. Save session cookies

## Flow (post)

1. Restore session or login
2. Open TikTok Studio upload
3. Select video file, caption, Post

## Diagnostics (no browser)

```bash
python diagnose.py policy
python diagnose.py login
python diagnose.py probe --limit 5
python diagnose.py otp --recipient 'you+alias@gmail.com'
python diagnose.py proxy --refresh
```

## Proxy

`autoProxy` defaults to **direct first**, then Tor SOCKS, then free HTTP pool. No manual proxy credentials required.

## Flags

| Flag | Effect |
|------|--------|
| `--dry-run` | Open signup page, probe locators, exit |
| `--ack-research-only` | Required for live runs (ToS research boundary) |
| `--no-restart` | Do not relaunch `bot.py` after success (Windows loop) |

## Tests

```bash
python -m pytest test_gmail_reader.py test_policy.py test_proxy_config.py test_accounts.py test_session_store.py test_proxy_auto.py -q
```

## Files

| File | Role |
|------|------|
| `cli.py` | Unified signup / login / post / pipeline |
| `bot.py` | Signup automation + policy gate |
| `login_flow.py` | Email login + OTP fallback |
| `post_flow.py` | Video upload via TikTok Studio |
| `accounts.py` | Load `users.txt` accounts |
| `session_store.py` | Cookie persistence |
| `policy.py` | ToS preflight + compliance checklist |
| `proxy_auto.py` | Direct / Tor / free proxy chain |
| `gmailReader.py` | IMAP OTP fetch |

See `docs/CASE_RESEARCH.md` and `docs/DIRECTIVE.md` for research notes.
