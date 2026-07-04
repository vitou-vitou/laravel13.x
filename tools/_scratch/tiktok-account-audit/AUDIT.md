# TikTok Account-Creation Repo Audit

**Method:** Spec-Kit checklist + Superpowers verification (static only)  
**Date:** 2026-06-18  
**Scratch path:** `tools/_scratch/tiktok-account-audit/`

## Scope boundary

| Allowed | Blocked |
|---------|---------|
| Clone, syntax check, static feature grep, pytest audit | Live Selenium against tiktok.com, real OTP, account creation |

TikTok bulk account automation violates [TikTok ToS](https://www.tiktok.com/legal/terms-of-service). This audit verifies **code presence**, not production execution.

## Target rule (from prior session)

Python + Selenium + undetected-chromedriver + Pixel 5 emulation + human-like interactions + IMAP OTP + Google Sheets logging + full onboarding flow.

## Repos cloned

1. [Andromeda606/TikTok-AccountCreator](https://github.com/Andromeda606/TikTok-AccountCreator)
2. [oomogo2000/AutoAccountTiktok](https://github.com/oomogo2000/AutoAccountTiktok)

## Feature matrix

| Feature | Andromeda606 | oomogo2000 | Rule |
|---------|:------------:|:----------:|:----:|
| Python | ✅ | ✅ | ✅ |
| Selenium | ✅ | ✅ | ✅ |
| undetected-chromedriver | ❌ | ✅ | ✅ |
| Pixel 5 emulation | ❌ | ❌ | ✅ |
| Human-like (delay + random + ActionChains) | ❌ | ❌ | ✅ |
| IMAP OTP | ✅ | ❌ | ✅ |
| Google Sheets | ❌ | ❌ | ✅ |
| TikTok signup URL | ✅ | ✅ | ✅ |

**Full rule match:** neither repo.

## Run commands (safe)

```bash
cd tools/_scratch/tiktok-account-audit
python -m py_compile Andromeda606-TikTok-AccountCreator/*.py
python -m py_compile oomogo2000-AutoAccountTiktok/*.py
python -m pytest test_feature_audit.py -v
```

## Legitimate alternative in laravel13.x

`tools/tiktok-metadata` — metadata-only CLI for licensed creator-commission ops (`--metadata-only`). Not account creation.
