#!/usr/bin/env bash
# Gmail OTP — gws primary, IMAP fallback.
set -euo pipefail

fetch_otp_gws() {
  if [[ -x "$REPO_ROOT/bin/gmail-tiktok-code" ]]; then
    "$REPO_ROOT/bin/gmail-tiktok-code" 2>/dev/null && return 0
  fi
  return 1
}

fetch_otp_imap() {
  local recipient="$1"
  local since_epoch="${2:-0}"
  local py="$REPO_ROOT/tools/tiktok-account-creator/gmailReader.py"
  [[ -f "$py" ]] || return 1
  local gmail_user gmail_pass
  gmail_user="$(full_gmail_address)"
  gmail_pass="$SETTINGS_GMAIL_PASS"
  [[ -n "$gmail_pass" ]] || return 1
  python3 - "$recipient" "$since_epoch" "$gmail_user" "$gmail_pass" <<'PY' 2>/dev/null || return 1
import sys, re, imaplib, email as emaillib
from email.utils import parsedate_to_datetime
recipient, since_epoch, user, passwd = sys.argv[1:5]
since_epoch = float(since_epoch)
code_re = re.compile(r"\b(\d{6})\b")
mail = imaplib.IMAP4_SSL("imap.gmail.com")
mail.login(user, passwd)
for box in ("INBOX", "[Gmail]/Spam", "[Gmail]/All Mail"):
    try:
        mail.select(box)
    except Exception:
        continue
    typ, data = mail.search(None, "ALL")
    if typ != "OK":
        continue
    for num in reversed(data[0].split()[-30:]):
        typ, msg_data = mail.fetch(num, "(RFC822)")
        if typ != "OK":
            continue
        msg = emaillib.message_from_bytes(msg_data[0][1])
        to_hdr = msg.get("To", "") + msg.get("Delivered-To", "")
        if recipient.lower() not in to_hdr.lower():
            continue
        frm = msg.get("From", "")
        if not re.search(r"tiktok|account\.tiktok", frm, re.I):
            continue
        try:
            dt = parsedate_to_datetime(msg.get("Date", ""))
            if dt.timestamp() < since_epoch - 5:
                continue
        except Exception:
            pass
        body = ""
        if msg.is_multipart():
            for part in msg.walk():
                if part.get_content_type() == "text/plain":
                    body += part.get_payload(decode=True).decode(errors="ignore")
        else:
            body = msg.get_payload(decode=True).decode(errors="ignore")
        m = code_re.search(body)
        if m and m.group(1) != "202510":
            print(m.group(1))
            sys.exit(0)
mail.logout()
sys.exit(1)
PY
}

poll_otp() {
  local recipient="$1"
  local since_epoch="${2:-$(date +%s)}"
  local max_wait="${OTP_MAX_WAIT:-120}"
  local poll="${OTP_POLL:-8}"
  local elapsed=0 code=""
  echo "Polling OTP for $recipient (max ${max_wait}s)..." >&2
  while [[ "$elapsed" -lt "$max_wait" ]]; do
    if code="$(fetch_otp_gws)"; then echo "$code"; return 0; fi
    if code="$(fetch_otp_imap "$recipient" "$since_epoch")"; then echo "$code"; return 0; fi
    sleep "$poll"
    elapsed=$((elapsed + poll))
  done
  return 1
}
