# ADB blocker (LDPlayer 9.5+)

## Symptom

```
adb devices
# (empty)
```

## Root cause

LDPlayer 9 disables host ADB by default (`basicSettings.adbDebug` missing or `0` in `vms/config/leidian{N}.config`).

## Fix (automated — preferred)

```bash
cd projects/amber-harbor-ll8b/publish
./amber-harbor-ll8b.exe enable-adb
# or
powershell -File ../src/AmberHarbor/Scripts/enable_adb.ps1
```

This sets `"basicSettings.adbDebug": 1` and `"basicSettings.rootMode": true` in `leidian0.config`, then reboots.

## Fix (manual one-liner)

Edit `D:/LDPlayer/LDPlayer9/vms/config/leidian0.config` and add:

```json
"basicSettings.adbDebug": 1,
```

Reboot the emulator. Verify:

```bash
D:/LDPlayer/LDPlayer9/adb.exe devices
# expect: 127.0.0.1:5555   device
```

## UI fallback (if config patch fails)

LDPlayer → gear (right toolbar) → **Advanced** → Root ON → ADB **Open local connection** → Save → Restart.

**Note:** Do not match Windows 11 "Settings" (`ApplicationFrameWindow`) — only the LDPlayer settings dialog (~756×766).

## After ADB works

```bash
./amber-harbor-ll8b.exe doctor
./amber-harbor-ll8b.exe cycle --ack-research-only
```
