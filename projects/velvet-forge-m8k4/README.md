# velvet-forge-m8k4

Standalone **C# / .NET 8 WinForms** Windows `.exe` for **Mobile Legends: Bang Bang** active hero play on **LDPlayer** — core TikTok farming gameplay capture loop.

| Command | What it does |
|---------|----------------|
| `preflight` | Launch emulator, verify MLBB + input backend |
| `probe` | Screenshot + UIAutomator when ADB available |
| `play-hero --hero Layla` | Classic → hero pick → active play (move + skills) |
| `cycle` | **Definition of done** — same as `play-hero` |
| `enable-adb` | One-time LDPlayer ADB helper (optional; window fallback works) |
| *(no args)* | WinForms control panel |

## Stack

| Layer | Choice |
|-------|--------|
| Language | C# / .NET 8 WinForms — **Windows .exe 100%** |
| Emulator | LDPlayer 9 (`ldconsole.exe` + bundled `adb.exe`) |
| Game | MLBB (`com.mobile.legends`) |
| Input | ADB `input tap/swipe` **or** Win32 `PostMessage` on bind HWND |
| UI | `uiautomator dump` when ADB on; normalized coords fallback |

## Blocker-aware design

| Blocker | Adaptation |
|---------|------------|
| ADB off / port refused | `inputBackend: "window"` — HWND taps via `list2` bind handle |
| MLBB not installed | `preflight` fails with install hint (Play Store in LDPlayer) |
| Hero UI varies by patch | UIAutomator text first; `GameLayout` coords fallback |
| LDPlayer 9.5 settings UI | Updated `Scripts/enable_adb.ps1` (right-toolbar gear) |

## Build

```powershell
cd projects/velvet-forge-m8k4
copy settings.example.json settings.json
dotnet publish src/VelvetForge/VelvetForge.csproj -c Release -o publish
```

Output: `publish/velvet-forge-m8k4.exe`

## Quick run

```bash
cd projects/velvet-forge-m8k4
cp settings.example.json settings.json
dotnet run --project src/VelvetForge -- preflight
dotnet run --project src/VelvetForge -- play-hero --hero Layla --duration 90
dotnet run --project src/VelvetForge -- cycle --hero Miya --duration 120
```

## Settings

```json
{
  "heroName": "Layla",
  "playDurationSeconds": 480,
  "inputBackend": "auto",
  "mlbbPackage": "com.mobile.legends"
}
```

Set `"inputBackend": "window"` to force HWND mode when ADB is blocked.

## Related

- `projects/amber-harbor-ll8b` — TikTok signup/post farm (same LDPlayer core)
- `projects/crisp-spark-4e97` — Shell/bash TikTok farm

## Prerequisites (real-case success)

1. **MLBB installed** in LDPlayer (`com.mobile.legends`) and **past create-account** (use an existing Moonton login for best results).
2. **LDPlayer visible** — do not cover the emulator window during `play-hero` / `cycle`.
3. **ADB (optional but recommended):** run `enable-adb` once → Settings → Other → ADB Open local connection. Enables UIAutomator hero/match detection.
4. **If ADB blocked:** set `"inputBackend": "window"` in `settings.json` (default fallback on this repo's LDPlayer 9.5).

## Verified on this machine (2026-06-18)

| Step | Result |
|------|--------|
| `probe` | Window backend, bind HWND, screenshots OK |
| `play-hero --hero Layla` | Exit 0, 19–30 active-play ticks logged |
| Blocker | Fresh emulator: create-character + LDStore mis-taps without ADB/OCR |

After manual account setup (or ADB + UIAutomator), re-run:

```bash
./bin/velvet-forge-m8k4 cycle --hero Layla --duration 480
```
