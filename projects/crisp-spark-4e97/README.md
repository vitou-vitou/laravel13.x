# crisp-spark-4e97

Standalone **Shell 100%** TikTok farm for **LDPlayer** visible window — Android TikTok app via `ldconsole` + ADB.

| Command | What it does |
|---------|----------------|
| `./farm.sh preflight` | Launch emulator, enable ADB, push sample video |
| `./farm.sh enable-adb` | One-time UI helper (LDPlayer Settings → ADB open local) |
| `./farm.sh probe` | UIAutomator probe (Profile / Sign up visible) |
| `./farm.sh signup --ack-research-only` | Email signup + Gmail OTP |
| `./farm.sh login` | Email/password login |
| `./farm.sh post` | Upload `assets/sample.mp4` |
| `./farm.sh cycle --ack-research-only` | **preflight → signup → login → post** |

## Stack

| Layer | Choice |
|-------|--------|
| Language | Bash / Shell 100% |
| Emulator | LDPlayer 9 (`ldconsole.exe` + bundled `adb.exe`) |
| App | TikTok Android (`com.zhiliaoapp.musically`) |
| UI | `uiautomator dump` + `input tap/text` |
| OTP | `bin/gmail-tiktok-code` → IMAP fallback |
| Host clicks | `scripts/enable_adb.ps1` (PowerShell, one-time ADB enable) |

## Prerequisites

1. **LDPlayer 9** installed (default path `D:/LDPlayer/LDPlayer9`)
2. **TikTok** installed inside the emulator (Play Store)
3. **Gmail OTP** configured (`tools/tiktok-farm-ts/settings.json` or local `settings.json`)

```bash
cd projects/crisp-spark-4e97
chmod +x farm.sh flows/*.sh lib/*.sh

cp settings.example.json settings.json
# edit ldplayerHome / email / gmailPass / password

./farm.sh enable-adb    # first run if adb devices empty
./farm.sh preflight
./farm.sh cycle --ack-research-only
```

## ADB blocker (common)

LDPlayer 9 ships with **ADB off by default**. Fix:

1. LDPlayer → Settings (gear on right toolbar) → **Other settings**
2. Enable **Root permission**
3. **ADB debugging** → **Open local connection**
4. Save → Restart

Or: `./farm.sh enable-adb` (automates the above via PowerShell clicks).

Verify:

```bash
D:/LDPlayer/LDPlayer9/adb.exe devices
# expect: emulator-5554 device
```

## Layout

```text
farm.sh              CLI
lib/                 ldplayer, adb, ui, touch, otp, accounts
flows/               preflight, probe, signup, login, post, cycle
scripts/             enable_adb.ps1, capture_window.ps1
assets/sample.mp4    test video
```

## Research boundary

Signup/cycle require `--ack-research-only` or `TIKTOK_RESEARCH_ACK=1`.

## Repo wrapper

```bash
./bin/crisp-spark-4e97 preflight
./bin/crisp-spark-4e97 cycle --ack-research-only
```

## Related

- `projects/swift-harbor-m7k2` — same flows via **agent-browser** (web TikTok)
- `tools/tiktok-farm-ts` — TypeScript variant
