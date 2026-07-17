#!/usr/bin/env sh
# Scheduled Postgres backups (Risk #2).
# Runs in a postgres:16-alpine container so pg_dump matches the server version.
# Writes timestamped gzipped dumps to /backups and prunes by retention.
set -eu

PGHOST="${DB_HOST:-postgres}"
PGPORT="${DB_PORT:-5432}"
PGUSER="${DB_USERNAME:-dashboard}"
PGPASSWORD="${DB_PASSWORD:-secret}"
PGDATABASE="${DB_DATABASE:-dashboard}"
export PGHOST PGPORT PGUSER PGPASSWORD PGDATABASE

BACKUP_DIR="${BACKUP_DIR:-/backups}"
KEEP_DAYS="${BACKUP_KEEP_DAYS:-7}"
INTERVAL="${BACKUP_INTERVAL:-86400}"   # seconds between runs (default daily)

mkdir -p "$BACKUP_DIR"

do_backup() {
  ts="$(date +%Y%m%d-%H%M%S)"
  out="$BACKUP_DIR/${PGDATABASE}-${ts}.sql.gz"
  echo "[backup] $(date -u +%FT%TZ) dumping ${PGDATABASE} -> ${out}"
  if pg_dump --no-owner --no-privileges | gzip -9 > "$out.tmp"; then
    mv "$out.tmp" "$out"
    echo "[backup] ok: $(du -h "$out" | cut -f1)"
  else
    echo "[backup] FAILED" >&2
    rm -f "$out.tmp"
    return 1
  fi

  # Retention: delete dumps older than KEEP_DAYS
  find "$BACKUP_DIR" -name "${PGDATABASE}-*.sql.gz" -type f -mtime "+${KEEP_DAYS}" -print -delete

  # Optional offsite: if rclone is available and BACKUP_RCLONE_REMOTE is set.
  # rclone is NOT installed in postgres:alpine by default — see docs/backups.md
  # for enabling offsite copy. Left as a no-op hook here.
  if [ -n "${BACKUP_RCLONE_REMOTE:-}" ] && command -v rclone >/dev/null 2>&1; then
    echo "[backup] offsite -> ${BACKUP_RCLONE_REMOTE}"
    rclone copy "$out" "$BACKUP_RCLONE_REMOTE" || echo "[backup] offsite copy FAILED" >&2
  fi
}

echo "[backup] service start: interval=${INTERVAL}s keep=${KEEP_DAYS}d dir=${BACKUP_DIR}"
while true; do
  do_backup || echo "[backup] cycle failed, will retry next interval" >&2
  sleep "$INTERVAL"
done
