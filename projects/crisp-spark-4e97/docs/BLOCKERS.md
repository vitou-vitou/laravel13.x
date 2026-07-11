# Blockers and workarounds (crisp-spark-4e97)

## 1. LDPlayer 9 ADB off by default

**Symptom:** `adb devices` empty; `ldconsole adb` → `emulator-5554 not found`.

**Fix (manual, reliable):**

1. LDPlayer title bar → menu (☰) → **Settings**
2. Tab **Other settings**
3. **Root permission** → ON
4. **ADB debugging** → **Open local connection**
5. **Save settings** → **Restart now**

Or: `./farm.sh enable-adb` (PowerShell UI helper — may need coordinate tuning on your display).

**Verify:**

```bash
D:/LDPlayer/LDPlayer9/adb.exe devices
# emulator-5554    device
```

## 2. TikTok not preinstalled

**Fix:** `./farm.sh install-tiktok` or Play Store → search TikTok → Install.

`ldconsole installapp --packagename com.zhiliaoapp.musically` may pull from LDStore.

## 3. Portrait resolution

Preflight sets `720x1280` for TikTok. Reboot applies change.

## 4. Touch-only mode (no ADB)

```bash
export FARM_MODE=touch
./farm.sh signup --ack-research-only
```

Uses `operaterecord` PutMultiTouch only — less reliable than UIAutomator.

## 5. Web fallback (same repo)

If LDPlayer ADB remains blocked, use sibling project:

```bash
cd projects/swift-harbor-m7k2
./farm.sh cycle --ack-research-only
```

Same Gmail OTP + `settings.json`; uses agent-browser instead of Android app.
