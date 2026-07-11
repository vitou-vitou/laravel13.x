---
name: tiktok-platform-policy-boundary
description: >-
  TikTok platform policy and ToS boundaries for automation in laravel13.x.
  Use before editing or running tools/tiktok-account-creator, bin/tiktok-signup-run,
  or any TikTok signup bot. Routes legitimate work to tools/tiktok-metadata.
---

# TikTok platform policy boundary

Use this skill when working on **TikTok automation** in `laravel13.x` — especially `tools/tiktok-account-creator`.

## Allowed vs blocked

| Path | Use when |
|------|----------|
| `tools/tiktok-metadata` | Creator consent; metadata-only or authorized backup |
| `bin/tiktok-signup-run` | Single account; human reviews form before submit |
| `tools/tiktok-account-creator` | **Local research only** — requires `--ack-research-only` |

| Blocked | Reason |
|---------|--------|
| Bulk automated signup loops | [TikTok ToS](https://www.tiktok.com/legal/terms-of-service) |
| Production account farms | Platform abuse + account bans |
| Hammering Send code / resend | Rate limits; IP/fingerprint blocks |

## Before running the Selenium bot

1. Read `tools/tiktok-account-creator/docs/CASE_RESEARCH.md`
2. Run `python diagnose.py login` and `python diagnose.py policy`
3. Pass **`--ack-research-only`** or set `TIKTOK_RESEARCH_ACK=1`
4. Log every attempt via `runs.csv` (`run_log.py`)
5. Respect `maxAccountsPerDay` in `settings.json`

## Operational rules (from incidents)

- Success = URL leaves `/signup/phone-or-email/email` (not just OTP filled)
- Reject OTP patterns like `202510` (date fragments)
- IMAP: filter by recipient alias + `since_epoch` after Send code
- Rate limit text: stop 30–60+ min; fresh profile/IP
- Delete **only** TikTok verification mail — never whole inbox

## Legitimate alternative

For creator-commission pilot work, use:

```bash
cd tools/tiktok-metadata
python scrape_tiktok.py --username HANDLE --limit 10 --metadata-only
```

See `docs/creator-commission/README.md`.

## Compliance checklist

Invoke **Legal Compliance Checker** (`docs/agency-agents/support/support-legal-compliance-checker.md`) for:

- Multi-jurisdictional data handling (Gmail IMAP, stored credentials)
- Audit trail (`runs.csv`, not committing `settings.json` / `users.txt`)
- Marketing vs automation boundary

## Related commands

```bash
cd tools/tiktok-account-creator
python diagnose.py policy
python bot.py --dry-run
python bot.py --ack-research-only --no-restart
python -m pytest test_gmail_reader.py test_policy.py -v
```
