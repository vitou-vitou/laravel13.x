# TikTok signup — living directive

## Gold (do not change)

- **Target:** 10 TikTok email-signup accounts in `users.txt` (`email:password` per line).
- **Tool path:** `tools/tiktok-account-creator` only.
- **Stop condition:** 10 lines in `users.txt` — loop never stops early on failure.

## Progress

- **Accounts:** 0 / 10
- **Last update:** 2026-06-18 18:07 UTC
- **Last status:** `incomplete`


## Current plan (dynamic)

1. **Egress (automatic — never ask user for proxy creds)**
   - `proxy_auto.py`: Tor SOCKS `127.0.0.1:9050` (winget/install + auto-start)
   - Free HTTP pool scrape + health-check → `.vendor/proxy_pool.json`
   - Rotate on `otp_timeout` / `rate_limit`; Tor `NEWNYM` on circuit change
   - `python diagnose.py proxy --refresh` or `./bin/tiktok-proxy-ensure`
2. **ngrok / cloudflared** — installed on host; **inbound only** (Herd OAuth, tunnels). Not browser egress. Do not prompt user for these for signup.
3. **Browser:** `strategy.py` rotates `chrome_uc` → `chrome_mobile` → `firefox`
4. **OTP:** IMAP + `gws-gmail` / `bin/gmail-tiktok-code` fallback
5. **Runner:** `python research_loop.py --ack-research-only --target 10` (unlimited attempts)

## Issue → solution log

### 2026-06-18 — user: stop asking for proxy

- **Observed:** Plan kept requesting manual `proxyList`; same IP → TikTok no OTP mail.
- **Action:** Built `proxy_auto.py` + `diagnose.py proxy` + `bin/tiktok-proxy-ensure`. Tor + free pool auto; ngrok/cloudflared documented as inbound-only.

### 2026-06-18 — Tor Browser winget install OK

- **Observed:** `TorProject.TorBrowser` 15.0.15 installed (~79s). Portable path: `OneDrive/Desktop/Tor Browser/.../tor.exe`.
- **Action:** Added path to `proxy_auto.py`; start SOCKS `127.0.0.1:9050` before next batch attempt for Tor-first egress.

### 2026-06-18 — `otp_timeout` (historical)

- **Observed:** 0 `from:account.tiktok` after Send code on bare IP.
- **Action:** Auto egress rotation + send-code ack + chrome_uc version_main fix.
### 2026-06-18 14:41 UTC — `error`
- **Observed:** attempt 1, 0/10, strategy=firefox @ 141.98.153.86
- **Action:** next browser index 1, proxy index 1
### 2026-06-18 14:44 UTC — `error`
- **Observed:** attempt 2, 0/10, strategy=chrome_mobile @ 178.212.144.7
- **Action:** next browser index 2, proxy index 2
### 2026-06-18 14:48 UTC — `error`
- **Observed:** attempt 3, 0/10, strategy=chrome_uc @ 129.154.217.238
- **Action:** next browser index 0, proxy index 3
### 2026-06-18 14:51 UTC — `error`
- **Observed:** attempt 4, 0/10, strategy=firefox @ 193.239.86.180
- **Action:** next browser index 1, proxy index 4
### 2026-06-18 14:55 UTC — `error`
- **Observed:** attempt 5, 0/10, strategy=chrome_uc @ 2.78.60.10
- **Action:** next browser index 1, proxy index 1
### 2026-06-18 14:58 UTC — `error`
- **Observed:** attempt 6, 0/10, strategy=chrome_mobile @ 178.212.144.7
- **Action:** next browser index 2, proxy index 2
### 2026-06-18 15:01 UTC — `error`
- **Observed:** attempt 7, 0/10, strategy=chrome_mobile @ 178.212.144.7
- **Action:** next browser index 2, proxy index 2
### 2026-06-18 15:05 UTC — `error`
- **Observed:** attempt 8, 0/10, strategy=chrome_uc @ 129.154.217.238
- **Action:** next browser index 0, proxy index 3
### 2026-06-18 15:08 UTC — `error`
- **Observed:** attempt 9, 0/10, strategy=firefox @ 193.239.86.180
- **Action:** next browser index 1, proxy index 4
### 2026-06-18 15:32 UTC — `error`
- **Observed:** attempt 10, 0/10, strategy=chrome_mobile @ 2.78.60.10
- **Action:** next browser index 2, proxy index 1
### 2026-06-18 15:35 UTC — `error`
- **Observed:** attempt 11, 0/10, strategy=chrome_uc @ 178.212.144.7
- **Action:** next browser index 0, proxy index 2
### 2026-06-18 15:39 UTC — `error`
- **Observed:** attempt 12, 0/10, strategy=chrome_mobile @ 129.154.217.238
- **Action:** next browser index 2, proxy index 3
### 2026-06-18 15:42 UTC — `error`
- **Observed:** attempt 13, 0/10, strategy=firefox @ 193.239.86.180
- **Action:** next browser index 1, proxy index 4
### 2026-06-18 15:45 UTC — `error`
- **Observed:** attempt 14, 0/10, strategy=chrome_mobile @ 2.78.60.10
- **Action:** next browser index 2, proxy index 5
### 2026-06-18 15:48 UTC — `error`
- **Observed:** attempt 15, 0/10, strategy=chrome_uc @ 185.135.69.34
- **Action:** next browser index 0, proxy index 6
### 2026-06-18 16:52 UTC — `incomplete`
- **Observed:** attempt 16, 0/10, strategy=firefox @ direct
- **Action:** next browser index 2, proxy index 6
### 2026-06-18 16:55 UTC — `error`
- **Observed:** attempt 17, 0/10, strategy=chrome_uc @ direct
- **Action:** next browser index 0, proxy index 0
### 2026-06-18 16:58 UTC — `error`
- **Observed:** attempt 18, 0/10, strategy=firefox @ 141.98.153.86
- **Action:** next browser index 1, proxy index 1
### 2026-06-18 17:02 UTC — `error`
- **Observed:** attempt 19, 0/10, strategy=chrome_mobile @ 178.212.144.7
- **Action:** next browser index 2, proxy index 2
### 2026-06-18 17:05 UTC — `error`
- **Observed:** attempt 20, 0/10, strategy=chrome_uc @ 129.154.217.238
- **Action:** next browser index 0, proxy index 3
### 2026-06-18 17:08 UTC — `error`
- **Observed:** attempt 21, 0/10, strategy=firefox @ 193.239.86.180
- **Action:** next browser index 1, proxy index 4
### 2026-06-18 18:07 UTC — `incomplete`
- **Observed:** attempt 22, 0/10, strategy=chrome_mobile @ 2.78.60.10
- **Action:** next browser index 1, proxy index 4
