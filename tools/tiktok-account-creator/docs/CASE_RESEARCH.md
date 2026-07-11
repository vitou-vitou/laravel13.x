# TikTok email signup — case research & development notes

**Project:** `tools/tiktok-account-creator`  
**Date:** 2026-06-18  
**Status:** Patched fork of Andromeda606; one verified browser fill + mixed OTP outcomes

---

## Executive summary

| Layer | Status | Notes |
|-------|--------|-------|
| Gmail app password | **OK** | IMAP login succeeds; 16-char password |
| 2026 TikTok DOM | **OK** | Birthday → email → password → send code → OTP → Next |
| Send code click | **OK** | Fixed `disabled` attribute handling |
| OTP via IMAP | **Fragile** | Depends on TikTok actually sending mail |
| False success | **Fixed** | Was clicking signup **Next** and saving `users.txt` |
| Wrong OTP `202510` | **Fixed** | Date fragment from non-TikTok mail; filter added |
| Rate limit | **Observed** | “Maximum number of attempts reached” |

**App password is not the problem.** Empty OTP means **no matching TikTok email** in the mailbox (or rate limit prevented send).

---

## TikTok verification email (research)

Sources: [OpenInbox TikTok demo](https://openinbox.io/demo-inbox/tiktok-verification), [SociallyIn](https://sociallyin.com/resources/tiktok-verification-code/), community reports.

| Field | Typical value |
|-------|----------------|
| Sender | `*@account.tiktok.com`, `*@tiktok.com`, sometimes `@bytedance.com` |
| Subject | “Verify your email address”, “Your login code is …” |
| Code | **6 digits**, numeric |
| TTL | ~5–15 minutes |
| Max attempts | ~3 wrong codes → temporary lockout |
| Cooldown | Often 30–60+ minutes after lockout |

Web signup may **not send email** when:

- Rate limited / too many attempts from same IP or browser fingerprint
- Captcha or risk check failed silently
- Disposable or plus-address flagged (Gmail `+alias` usually works)
- Region / VPN mismatch

---

## Incidents in this repo

### 1. False success (`202510`)

- IMAP returned `202510` from a **YYYYMM** fragment in an unrelated “verification” email (Dropbox, Alibaba, etc.).
- Bot matched signup **Next** button in `finish_success_if_possible()` and wrote `users.txt`.

**Fixes:**

- Reject `^(19|20)\d{4}$` OTP patterns
- Remove generic `\b(\d{6})\b` fallback
- Success only when URL leaves `/signup/phone-or-email/email`
- Never treat signup **Next** as post-signup

### 2. `ModuleNotFoundError: gmailReader`

- File missing from scratch copy; restored + `sys.path` bootstrap.

### 3. Rate limit

- Screenshot: red OTP field, “Maximum number of attempts reached.”
- Inbox had **0** `FROM tiktok` messages — TikTok likely never sent (or `deletemail()` had wiped old TikTok mail).

### 4. `deletemail()` deleted entire INBOX

- Original upstream behavior; dangerous on a real Gmail account.

**Fix:** `delete_tiktok_verification_mail()` — only TikTok verification messages.

---

## Architecture (current)

```
bot.py
  ├─ Firefox + geckodriver
  ├─ Fill 2026 signup form
  ├─ click_send_code() → records send_code_epoch
  ├─ getGmail() → gmailReader.getmail(recipient, since_epoch)
  ├─ Register() → OTP + Next + error detection
  └─ finish_success() → users.txt + safe mail cleanup

gmailReader.py
  ├─ probe_inbox() — diagnostics
  ├─ getmail(recipient, since_epoch)
  └─ delete_tiktok_verification_mail()

diagnose.py
  ├─ login   — test app password
  ├─ probe   — list candidate emails
  └─ otp     — extract code CLI
```

---

## CLI reference

```bash
cd tools/tiktok-account-creator

# IMAP only
python diagnose.py login
python diagnose.py probe --limit 5
python diagnose.py otp --recipient 'you+alias@gmail.com'

# Browser
python bot.py --dry-run          # locators only
python bot.py --no-restart       # no os.startfile loop
python bot.py

# Tests
python -m pytest test_gmail_reader.py -v
```

---

## Operational playbook

1. **Before run:** `python diagnose.py login`
2. **After Send code in browser:** Gmail search `from:account.tiktok OR from:tiktok`
3. **If no mail in 2 min:** rate limit or risk block — wait 30–60 min, new Firefox profile/IP
4. **If mail arrives but OTP empty:** `python diagnose.py probe` — tune `CODE_PATTERNS`
5. **On success:** confirm URL is username/FYP, then check `users.txt`

---

## Future development (not implemented)

| Item | Why |
|------|-----|
| ~~Policy gate + audit log~~ | **Done** — `policy.py`, `run_log.py`, `--ack-research-only` |
| ~~Username step automation~~ | **Done** — `/signup/create-username` handler |
| ~~Resend code with backoff~~ | **Done** — bounded resend after attempt 18 |
| ~~Firefox profile rotation~~ | **Done** — temp profile per Firefox session |
| ~~Chrome mobile / UC option~~ | **Done** — `browser`: `chrome_mobile`, `chrome_uc` |
| Google Sheets API logging | CSV `runs.csv` today; import or add service account later |
| Captcha / OpenCV | Upstream README note; hard blocker |

---

## Legal / ToS

Automated bulk account creation violates [TikTok Terms of Service](https://www.tiktok.com/legal/terms-of-service). This project is for **local research and education** in a private tooling repo.

**Guardrails added:**

- `python diagnose.py policy` — checklist + summary
- `python bot.py --ack-research-only` — explicit research acknowledgment
- `maxAccountsPerDay` in `settings.json` — optional cap from `runs.csv`
- Legitimate alternative: `tools/tiktok-metadata` (creator consent)
- Agent skill: `.agents/skills/tiktok-platform-policy-boundary/SKILL.md`
