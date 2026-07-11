# TikTok farm window

Separate **visible-browser** tool for TikTok farming core flows:

| Command | What it does |
|---------|----------------|
| `probe` | Dry-run signup page locators (no submit) |
| `signup` | Email signup + Gmail OTP (reuses `tiktok-account-creator`) |
| `login` | Email/password login with persistent profile |
| `post` | Upload MP4 via TikTok web studio |
| `cycle` | **signup → login → post** (definition of done) |

Settings live in `../tiktok-account-creator/settings.json` (single source).

## Setup

```bash
cd tools/tiktok-farm-window
pip install -r requirements.txt
python make_sample_video.py   # needs ffmpeg, or drop any short .mp4 in assets/sample.mp4
```

Copy `../tiktok-account-creator/settings.example.json` → `settings.json` if missing.

## Research boundary

Signup and `cycle` require **`--ack-research-only`** (same policy as account-creator).

```bash
python main.py probe
python main.py signup --ack-research-only
python main.py login
python main.py post --caption "hello from farm window"
python main.py cycle --ack-research-only
```

## Architecture

- `window_browser.py` — visible desktop window + per-account profile under `.profiles/`
- `flows/signup.py` — mobile-emulated signup (delegates to `bot.run_signup_flow`)
- `flows/login.py` — email login at `/login/phone-or-email/email`
- `flows/post.py` — TikTok Studio upload
- `session_store.py` — `accounts.json` (+ migrates `users.txt`)

## Tests

```bash
python -m pytest test_session_store.py test_paths.py -v
```

Policy skill: `.agents/skills/tiktok-platform-policy-boundary/SKILL.md`
