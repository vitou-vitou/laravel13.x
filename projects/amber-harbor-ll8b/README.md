# amber-harbor-ll8b

Standalone **C# / .NET 8 WinForms** Windows `.exe` for TikTok farming on **LDPlayer** — signup, login, post via `ldconsole` + ADB + UIAutomator.

| Command | What it does |
|---------|----------------|
| `preflight` | Launch emulator, enable ADB, verify TikTok + sample video |
| `probe` | UIAutomator probe (Profile / Sign up visible) |
| `signup --ack-research-only` | Email signup + Gmail OTP |
| `login` | Email/password login |
| `post` | Upload `assets/sample.mp4` |
| `cycle --ack-research-only` | **preflight → signup → login → post** (definition of done) |
| `fleet init --count N` | Clone template LDPlayer instances + `data/fleet.json` |
| `setup --index N` | Install Surfshark + TikTok, USA VPN, adbDebug |
| `location verify --index N` | ipinfo.io US check inside emulator |
| `enable-adb` | Patch `leidian{N}.config` adbDebug + reboot |
| *(no args)* | Launch WinForms control panel |

## Stack

| Layer | Choice |
|-------|--------|
| Language | C# / .NET 8 WinForms — **Windows .exe 100%** |
| Emulator | LDPlayer 9 (`ldconsole.exe` + bundled `adb.exe`) |
| App | TikTok Android (`com.zhiliaoapp.musically`) |
| UI | `uiautomator dump` + `input tap/text` |
| OTP | `bin/gmail-tiktok-code` → MailKit IMAP fallback |

## Build

```powershell
cd projects/amber-harbor-ll8b
dotnet publish src/AmberHarbor/AmberHarbor.csproj -c Release -o publish
```

Output: `publish/amber-harbor-ll8b.exe` (self-contained win-x64)

## Setup

1. LDPlayer 9 at `D:/LDPlayer/LDPlayer9`
2. TikTok installed in emulator (Play Store)
3. Copy `settings.example.json` → `settings.json` (Gmail app password + TikTok password)

```bash
cd projects/amber-harbor-ll8b
cp settings.example.json settings.json
dotnet run --project src/AmberHarbor -- preflight
dotnet run --project src/AmberHarbor -- cycle --ack-research-only
```

## ADB blocker

LDPlayer 9 disables host ADB by default (`basicSettings.adbDebug` missing in `leidian{N}.config`).

```bash
./amber-harbor-ll8b.exe enable-adb   # patches config + reboots (no UI clicks)
./amber-harbor-ll8b.exe doctor
```

See **`docs/BLOCKER_ADB.md`** for manual fallback.

## Multi-instance fleet + Surfshark VPN

10–20 LDPlayer instances with per-index ADB (`5555 + 2×index`), Surfshark USA, and location verify before TikTok.

```bash
./amber-harbor-ll8b.exe fleet init --count 10
./amber-harbor-ll8b.exe vpn ensure --index 3
./amber-harbor-ll8b.exe cycle --index 3 --ack-research-only
./amber-harbor-ll8b.exe cycle --index 3 --skip-vpn --ack-research-only   # until Surfshark on template
```

See **`docs/FLEET_VPN.md`** (Phase 0: install Surfshark on template index 0 first).

## Research boundary

Signup/cycle require `--ack-research-only` or `TIKTOK_RESEARCH_ACK=1`. See `.agents/skills/tiktok-platform-policy-boundary/SKILL.md`.

## Related

- `projects/crisp-spark-4e97` — Shell/bash port (same flows)
- `tools/tiktok-farm-window` — visible browser (web TikTok)
