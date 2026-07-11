#!/usr/bin/env bash
# Load settings.json without jq (Shell 100%).
set -euo pipefail

json_get_string() {
  local key="$1" file="$2"
  grep -E "\"${key}\"[[:space:]]*:" "$file" | head -1 \
    | sed -E 's/.*:[[:space:]]*"([^"]*)".*/\1/'
}

json_get_number() {
  local key="$1" file="$2"
  grep -E "\"${key}\"[[:space:]]*:" "$file" | head -1 \
    | sed -E 's/.*:[[:space:]]*([0-9]+).*/\1/'
}

load_settings() {
  if [[ ! -f "$SETTINGS_FILE" ]]; then
    echo "Missing $SETTINGS_FILE — copy settings.example.json or symlink from tools/tiktok-farm-ts/settings.json" >&2
    return 1
  fi
  SETTINGS_EMAIL_USER="$(json_get_string email "$SETTINGS_FILE")"
  SETTINGS_EMAIL_DOMAIN="$(json_get_string eMailEnd "$SETTINGS_FILE")"
  SETTINGS_GMAIL_PASS="$(json_get_string gmailPass "$SETTINGS_FILE")"
  SETTINGS_PASSWORD="$(json_get_string password "$SETTINGS_FILE")"
  SETTINGS_MAX_PER_DAY="$(json_get_number maxAccountsPerDay "$SETTINGS_FILE")"
  SETTINGS_MAX_PER_DAY="${SETTINGS_MAX_PER_DAY:-3}"
  export SETTINGS_EMAIL_USER SETTINGS_EMAIL_DOMAIN SETTINGS_GMAIL_PASS SETTINGS_PASSWORD SETTINGS_MAX_PER_DAY
}

alias_email() {
  local suffix="${1:-farm}"
  local stamp
  stamp="$(date +%s | tail -c 5)"
  echo "${SETTINGS_EMAIL_USER}+${suffix}${stamp}@${SETTINGS_EMAIL_DOMAIN}"
}

full_gmail_address() {
  echo "${SETTINGS_EMAIL_USER}@${SETTINGS_EMAIL_DOMAIN}"
}
