# Optional APK sideloads (when Play Store / installapp fails)

Drop APK files here (exact names):

| File | App |
|------|-----|
| `surfshark.apk` | Surfshark VPN |
| `tiktok.apk` | TikTok |

Or set full paths in `settings.json` → `apps.surfsharkApk` / `apps.tiktokApk`.

Then run:

```bash
amber-harbor-ll8b.exe setup --index 0
```
