#!/usr/bin/env bash
# Gmail OTP for TikTok signup — gws primary, IMAP via gmailReader.py
set -euo pipefail

LAST30DAYS_PYTHON=""
for py in py python3 python; do
  if command -v "$py" >/dev/null 2>&1; then
    if "$py" -c 'import sys; sys.exit(0)' 2>/dev/null; then
      LAST30DAYS_PYTHON="$py"
      break
    fi
  fi
done
if [[ "$LAST30DAYS_PYTHON" == "py" ]]; then
  LAST30DAYS_PYTHON="py -3"
fi

fetch_otp_gws() {
  if [[ -x "$REPO_ROOT/bin/gmail-tiktok-code" ]]; then
    "$REPO_ROOT/bin/gmail-tiktok-code" 2>/dev/null && return 0
  fi
  return 1
}

# TikTok often sends To: base@gmail.com even when signup used plus-addressing.
imap_recipient_candidates() {
  local recipient="$1"
  local base
  base="$(full_gmail_address)"
  printf '%s\n' "$recipient" "$base" ""
}

fetch_otp_imap_once() {
  local recipient="$1"
  local since_epoch="$2"
  local creator="$REPO_ROOT/tools/tiktok-account-creator"
  [[ -d "$creator" ]] || return 1
  [[ -n "$LAST30DAYS_PYTHON" ]] || return 1

  (
    cd "$creator"
    $LAST30DAYS_PYTHON -c "
from gmailReader import getmail
import sys
rec = sys.argv[1] if sys.argv[1] else None
code = getmail(recipient=rec, since_epoch=float(sys.argv[2]))
if code:
    print(code)
    sys.exit(0)
sys.exit(1)
" "$recipient" "$since_epoch"
  ) 2>/dev/null
}

fetch_otp_imap() {
  local recipient="$1"
  local since_epoch="$2"
  local cand code
  while IFS= read -r cand; do
    if code="$(fetch_otp_imap_once "$cand" "$since_epoch")"; then
      echo "$code"
      return 0
    fi
  done < <(imap_recipient_candidates "$recipient")
  return 1
}

poll_otp() {
  local recipient="$1"
  local since_epoch="${2:-$(date +%s)}"
  local max_wait="${OTP_MAX_WAIT:-180}"
  local poll="${OTP_POLL:-6}"
  local elapsed=0 code=""

  echo "Polling OTP for $recipient (max ${max_wait}s, IMAP fallback)..." >&2
  while [[ "$elapsed" -lt "$max_wait" ]]; do
    if code="$(fetch_otp_gws)"; then
      echo "$code"
      return 0
    fi
    if code="$(fetch_otp_imap "$recipient" "$since_epoch")"; then
      echo "$code"
      return 0
    fi
    sleep "$poll"
    elapsed=$((elapsed + poll))
  done
  return 1
}
