# Database Backups (Risk #2)

A `backup` service runs alongside the stack. It uses `postgres:16-alpine` (so
`pg_dump` matches the server version), dumps the database on an interval, gzips
it, and prunes old dumps.

## How it works

- Script: `docker/backup.sh`
- Storage: `pgbackups` named volume, mounted at `/backups`
- File format: `dashboard-YYYYMMDD-HHMMSS.sql.gz`
- Reads DB credentials from `.env` (`DB_HOST/PORT/USERNAME/PASSWORD/DATABASE`)

## Configuration (env)

| Var | Default | Meaning |
|-----|---------|---------|
| `BACKUP_INTERVAL` | `86400` | Seconds between dumps (daily) |
| `BACKUP_KEEP_DAYS` | `7` | Retention; older dumps deleted |
| `BACKUP_DIR` | `/backups` | In-container dump directory |
| `BACKUP_RCLONE_REMOTE` | _(unset)_ | Offsite target (requires rclone, see below) |

Set these in `.env` to override.

## List / inspect backups

```powershell
docker compose exec backup ls -lh /backups
```

## Restore

> WARNING: this overwrites the current database. Take a fresh dump first.

```powershell
# 1. Pick a dump
docker compose exec backup ls /backups

# 2. Restore it (replace FILE)
docker compose exec backup sh -c "gunzip -c /backups/FILE.sql.gz | psql -h postgres -U dashboard -d dashboard"
```

Or copy a dump to the host:

```powershell
docker compose cp backup:/backups/FILE.sql.gz ./FILE.sql.gz
```

## Manual one-off backup

```powershell
docker compose exec backup sh -c "pg_dump -h postgres -U dashboard --no-owner --no-privileges dashboard | gzip > /backups/manual-$(date +%Y%m%d-%H%M%S).sql.gz"
```

## Offsite copy (recommended for production)

The `pgbackups` volume lives on the same host as the database — that is NOT a
real disaster-recovery backup. For production, copy dumps offsite (S3, B2,
another host). Options:

1. **rclone in a custom image** — build a small image `FROM postgres:16-alpine`
   that adds rclone, set `BACKUP_RCLONE_REMOTE=myremote:bucket/path`, mount the
   rclone config. The script auto-copies each dump.
2. **Host-side sync** — bind-mount `pgbackups` to a host path and run
   `aws s3 sync` / `rclone sync` from a host cron.
3. **Managed Postgres** — move the DB to a managed provider with built-in PITR
   (see hardening proposal, Risk #2 option B).

## Verify restores

A backup you have never restored is not a backup. Schedule a quarterly
restore drill into a throwaway database and confirm row counts.
