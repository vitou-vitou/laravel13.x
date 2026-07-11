# swift-harbor-m7k2

Standalone **Shell 100%** TikTok farm window tool — visible browser via [agent-browser](https://www.npmjs.com/package/agent-browser).

| Command | What it does |
|---------|----------------|
| `./farm.sh probe` | Dry-run signup locators |
| `./farm.sh signup --ack-research-only` | Email signup + Gmail OTP |
| `./farm.sh login` | Email/password login (per-account session) |
| `./farm.sh post` | Upload `assets/sample.mp4` |
| `./farm.sh cycle --ack-research-only` | **signup → login → post** (definition of done) |

## Stack

| Layer | Choice |
|-------|--------|
| Language | Bash / Shell 100% |
| Browser | agent-browser (headed Chrome via CDP) |
| OTP | `bin/gmail-tiktok-code` (gws) → IMAP fallback |
| Accounts | `data/accounts.json` |

## Setup

```bash
cd projects/swift-harbor-m7k2
chmod +x farm.sh flows/*.sh lib/*.sh

# Settings (do not commit)
cp settings.example.json settings.json
# or symlink existing:
# ln -sf ../../tools/tiktok-farm-ts/settings.json settings.json

npm install -g agent-browser
agent-browser install
```

Gmail OTP (one-time):

```bash
cd ../.. && ./bin/gws-gmail-prep
```

## Blockers (plan adapts)

| Blocker | Detection | Mitigation |
|---------|-----------|------------|
| TikTok rate limit | `Maximum number of attempts reached` | Wait 30–60 min; `TIKTOK_USE_TOR=1` (slow); fresh IP |
| OTP to base Gmail | Plus-alias signup | IMAP matches `pakvitou168@gmail.com` too |
| gws not authed | `gws-gmail-prep` | IMAP via `settings.json` `gmailPass` |
| agent-browser daemon | `--headed ignored` | `agent-browser close --all` (auto in `lib/daemon.sh`) |

Resume after manual Send code:

```bash
EMAIL='you+tag@gmail.com' CODE=123456 ./farm.sh enter-code 123456
```

## Research boundary

Signup and `cycle` require **`--ack-research-only`** or `TIKTOK_RESEARCH_ACK=1`.

See `.agents/skills/tiktok-platform-policy-boundary/SKILL.md`.

## Layout

```text
farm.sh           CLI entry
lib/              paths, settings, browser, otp, accounts, policy
flows/            probe, signup, login, post, cycle
assets/sample.mp4 short test video
data/accounts.json persisted accounts (gitignored)
```

## Repo wrapper

```bash
./bin/swift-harbor-m7k2 probe
./bin/swift-harbor-m7k2 cycle --ack-research-only
```

## New project slug

```bash
./farm.sh new-name   # e.g. brisk-relay-a3f1
```

Copy this folder and rename to the generated slug for the next fork.
