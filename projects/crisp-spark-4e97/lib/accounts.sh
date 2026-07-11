#!/usr/bin/env bash
# Account store — Shell 100% (node merge when available).
set -euo pipefail

accounts_ensure() {
  mkdir -p "$(dirname "$ACCOUNTS_FILE")"
  if [[ ! -f "$ACCOUNTS_FILE" ]]; then
    echo '[]' >"$ACCOUNTS_FILE"
  fi
}

accounts_upsert() {
  local email="$1" password="$2"
  accounts_ensure
  local now
  now="$(date -u +%Y-%m-%dT%H:%M:%SZ)"
  if command -v node >/dev/null 2>&1; then
    node - "$ACCOUNTS_FILE" "$email" "$password" "$now" <<'NODE'
const fs = require("fs");
const [file, email, password, now] = process.argv.slice(2);
let list = [];
try { list = JSON.parse(fs.readFileSync(file, "utf8")); } catch { list = []; }
if (!Array.isArray(list)) list = list.accounts || [];
const i = list.findIndex((a) => a.email === email);
if (i >= 0) list[i].password = password;
else list.push({ email, password, createdAt: now, posts: 0 });
fs.writeFileSync(file, JSON.stringify(list, null, 2));
NODE
  else
    echo "[{\"email\":\"$email\",\"password\":\"$password\",\"createdAt\":\"$now\",\"posts\":0}]" >"$ACCOUNTS_FILE"
  fi
}

accounts_latest_email() {
  accounts_ensure
  if ! command -v node >/dev/null 2>&1; then
    grep -oE '"email"[[:space:]]*:[[:space:]]*"[^"]+"' "$ACCOUNTS_FILE" | tail -1 | sed 's/.*"\([^"]*\)"$/\1/'
    return
  fi
  node - "$ACCOUNTS_FILE" <<'NODE'
const fs = require("fs");
const list = JSON.parse(fs.readFileSync(process.argv[1], "utf8"));
const last = Array.isArray(list) ? list.at(-1) : null;
if (last?.email) console.log(last.email);
NODE
}

accounts_latest_password() {
  accounts_ensure
  command -v node >/dev/null 2>&1 || return 1
  node - "$ACCOUNTS_FILE" <<'NODE'
const fs = require("fs");
const list = JSON.parse(fs.readFileSync(process.argv[1], "utf8"));
const last = Array.isArray(list) ? list.at(-1) : null;
if (last?.password) console.log(last.password);
NODE
}

accounts_mark_login() {
  local email="$1"
  command -v node >/dev/null 2>&1 || return 0
  node - "$ACCOUNTS_FILE" "$email" <<'NODE'
const fs = require("fs");
const [file, email] = process.argv.slice(2);
const list = JSON.parse(fs.readFileSync(file, "utf8"));
const acc = list.find((a) => a.email === email);
if (acc) { acc.lastLogin = new Date().toISOString(); fs.writeFileSync(file, JSON.stringify(list, null, 2)); }
NODE
}

accounts_mark_post() {
  local email="$1"
  command -v node >/dev/null 2>&1 || return 0
  node - "$ACCOUNTS_FILE" "$email" <<'NODE'
const fs = require("fs");
const [file, email] = process.argv.slice(2);
const list = JSON.parse(fs.readFileSync(file, "utf8"));
const acc = list.find((a) => a.email === email);
if (acc) {
  acc.lastPost = new Date().toISOString();
  acc.posts = (acc.posts || 0) + 1;
  fs.writeFileSync(file, JSON.stringify(list, null, 2));
}
NODE
}

accounts_list() {
  accounts_ensure
  cat "$ACCOUNTS_FILE"
}
