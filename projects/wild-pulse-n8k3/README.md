# wild-pulse-n8k3

Standalone **Python 100%** LDPlayer window controller for **Mobile Legends: Bang Bang** active hero play — TikTok-farm sidecar core feature.

| Command | What it does |
|---------|----------------|
| `python play.py preflight` | Launch LDPlayer, probe MLBB (ADB or host vision) |
| `python play.py install` | Install MLBB via `installapp` / LDStore |
| `python play.py play-hero` | Onboard → practice mode → pick hero → active play loop |
| `python play.py cycle` | **preflight → play-hero** (definition of done) |

## Stack

| Layer | Choice |
|-------|--------|
| Language | **Python 3.12+** (OpenCV + host capture) |
| Emulator | LDPlayer 9 (`ldconsole.exe` + `operaterecord`) |
| Game | MLBB `com.mobile.legends` |
| Vision | OpenCV color/heuristic state machine |
| Input | `operaterecord` touch (ADB optional fallback) |
| Capture | PowerShell `capture_window.ps1` (largest `dnplayer` HWND) |

## Why Python (not C#/Shell)

- OpenCV template + color detection for game states
- Same repo pattern as `tools/tiktok-farm-window` but for native MLBB
- `adbauto`-style loop without extra native deps
- LDPlayer `operaterecord` already proven in `projects/crisp-spark-4e97`

## Setup

1. LDPlayer 9 at `D:/LDPlayer/LDPlayer9`
2. Install MLBB (Play Store / LDStore) — or `python play.py install`
3. Copy settings:

```bash
cd projects/wild-pulse-n8k3
cp settings.example.json settings.json
pip install -r requirements.txt
```

## Run (definition of done)

```bash
python play.py cycle --hero-index 2
```

Success = onboarding cleared, hero selected, **in_match** detected during active play (joystick + attack + skills loop).

## ADB blocker

LDPlayer ships with ADB off. This tool **does not require ADB** — host window capture + `operaterecord` are the primary path. Optional: `scripts/enable_adb.ps1` for `pm path` checks.

## Layout

```text
play.py                 CLI
wild_pulse/
  ldplayer.py           ldconsole wrapper
  capture.py            HWND screenshot + crop 1600x900
  touch.py              operaterecord + adb tap
  vision.py             screen state machine
  flows/                preflight, onboarding, play_hero
scripts/                capture_window.ps1, enable_adb.ps1
tmp/screenshots/        run artifacts
```

## Related

- `projects/amber-harbor-ll8b` — TikTok farm (C# / LDPlayer)
- `projects/crisp-spark-4e97` — TikTok farm (Shell / LDPlayer)
- `.agents/skills/tiktok-platform-policy-boundary` — TikTok automation boundary (MLBB play is separate)

## Research boundary

MLBB automation is for **local emulator research / content sidecar** only. Do not use for ranked cheating or ToS violations.
