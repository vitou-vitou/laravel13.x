#!/usr/bin/env bash
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
    echo "Missing $SETTINGS_FILE — cp settings.example.json settings.json" >&2
    return 1
  fi
  SETTINGS_EMAIL_USER="$(json_get_string email "$SETTINGS_FILE")"
  SETTINGS_EMAIL_DOMAIN="$(json_get_string eMailEnd "$SETTINGS_FILE")"
  SETTINGS_GMAIL_PASS="$(json_get_string gmailPass "$SETTINGS_FILE")"
  SETTINGS_PASSWORD="$(json_get_string password "$SETTINGS_FILE")"
  SETTINGS_MAX_PER_DAY="$(json_get_number maxAccountsPerDay "$SETTINGS_FILE")"
  SETTINGS_MAX_PER_DAY="${SETTINGS_MAX_PER_DAY:-3}"
  SETTINGS_LDPLAYER_HOME="$(json_get_string ldplayerHome "$SETTINGS_FILE")"
  SETTINGS_LDPLAYER_HOME="${SETTINGS_LDPLAYER_HOME:-${LDPLAYER_HOME:-D:/LDPlayer/LDPlayer9}}"
  SETTINGS_LDPLAYER_INDEX="$(json_get_number ldplayerIndex "$SETTINGS_FILE")"
  SETTINGS_LDPLAYER_INDEX="${SETTINGS_LDPLAYER_INDEX:-0}"
  SETTINGS_TIKTOK_PKG="$(json_get_string tiktokPackage "$SETTINGS_FILE")"
  SETTINGS_TIKTOK_PKG="${SETTINGS_TIKTOK_PKG:-com.zhiliaoapp.musically}"
  export SETTINGS_EMAIL_USER SETTINGS_EMAIL_DOMAIN SETTINGS_GMAIL_PASS SETTINGS_PASSWORD
  export SETTINGS_MAX_PER_DAY SETTINGS_LDPLAYER_HOME SETTINGS_LDPLAYER_INDEX SETTINGS_TIKTOK_PKG
  export LDPLAYER_INDEX="$SETTINGS_LDPLAYER_INDEX"
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
