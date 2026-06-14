# Shared Gmail verification-code fetch helpers (source from bin/* scripts).
# shellcheck shell=bash

mail_code_extract() {
  local text="$1"
  local regex="${CODE_REGEX:-[0-9]{6}}"
  echo "$text" | grep -oE "$regex" | head -1
}

mail_code_require_gws() {
  if ! command -v gws >/dev/null 2>&1; then
    echo "gws not installed. Run: npm install -g @googleworkspace/cli" >&2
    return 1
  fi
  if gws auth status 2>/dev/null | grep -q '"auth_method": "none"'; then
    echo "gws not authenticated. Run: gws auth login --services gmail --readonly" >&2
    return 1
  fi
  return 0
}

# Fetch one matching message. On success prints tab-separated fields:
#   code<TAB>message_id<TAB>subject<TAB>from
mail_code_fetch_gmail_once() {
  local query="${1:?query required}"
  local triage id body code subject from_line

  triage="$(gws gmail +triage --query "$query" --max 5 --format json 2>/dev/null || true)"
  [[ -n "$triage" && "$triage" != "[]" && "$triage" != "null" ]] || return 1

  id="$(echo "$triage" | grep -oE '"id"[[:space:]]*:[[:space:]]*"[^"]+"' | head -1 | sed 's/.*"\([^"]*\)"$/\1/')"
  [[ -n "$id" ]] || return 1

  subject="$(echo "$triage" | grep -oE '"subject"[[:space:]]*:[[:space:]]*"[^"]*"' | head -1 | sed 's/.*"\([^"]*\)"$/\1/' || true)"
  from_line="$(echo "$triage" | grep -oE '"from"[[:space:]]*:[[:space:]]*"[^"]*"' | head -1 | sed 's/.*"\([^"]*\)"$/\1/' || true)"

  body="$(gws gmail +read --id "$id" --format text 2>/dev/null || true)"
  code="$(mail_code_extract "$body")"
  [[ -n "$code" ]] || code="$(mail_code_extract "$triage")"
  [[ -n "$code" ]] || return 1

  printf '%s\t%s\t%s\t%s\n' "$code" "$id" "${subject:-}" "${from_line:-}"
  return 0
}

mail_code_cache_dir() {
  echo "${MAIL_CODE_CACHE_DIR:-$HOME/.cache/mail-code-notifier}"
}

mail_code_last_id_file() {
  mkdir -p "$(mail_code_cache_dir)"
  echo "$(mail_code_cache_dir)/last-id"
}

mail_code_is_duplicate() {
  local message_id="$1"
  local cache_file
  cache_file="$(mail_code_last_id_file)"
  [[ -f "$cache_file" ]] && [[ "$(cat "$cache_file")" == "$message_id" ]]
}

mail_code_mark_notified() {
  local message_id="$1"
  echo "$message_id" >"$(mail_code_last_id_file)"
}
