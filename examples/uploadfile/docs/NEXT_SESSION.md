# Next session — uploadfile

**Updated:** 2026-07-18

## Status

| Item | Status |
|------|--------|
| Spec-Kit | `001-uploadfile` — **MVP complete** (T001–T005 done) |
| Design | `docs/superpowers/specs/2026-07-18-uploadfile-example-design.md` (repo root) |
| Herd | http://uploadfile.test |
| Tests | 13/13 (`php artisan test`) — `Storage::fake`, upload/list/download/delete + traversal guard |
| API | `POST/GET /api/files`, `GET/DELETE /api/files/{filename}` — no auth, no DB |

## Run

```bash
export PATH="/d/laravel13.x/bin:$PATH"
cd /d/laravel13.x/examples/uploadfile
php artisan test
npm run dev
```

Browser: **http://uploadfile.test** (Herd — no `artisan serve` needed)

| Command | What it does |
|---------|----------------|
| `npm run dev` | Vite HMR (Herd serves PHP) |
| `npm run vite` | Vite only |

## Next agent steps

MVP is complete — do not re-scaffold or rework the core four endpoints. Optional post-MVP (OpenSpec change orders only):

1. Public disk + `storage:link` demo
2. Multiple-file upload or streamed download
3. DB metadata table (original filename on download)

## Pitfalls

- `php: command not found` → `export PATH="/d/laravel13.x/bin:$PATH"`
- Opened :5173 instead of **http://uploadfile.test**
- 500 Unsupported cipher → `./bin/fix-example-app-key uploadfile` (bad APP_KEY / ANSI)
- Health check → `./bin/verify-example uploadfile`
- OAuth/ngrok on Herd → traffic policy + `127.0.0.1:80` (not `ngrok http http://uploadfile.test`) — `docs/EXAMPLE_DEV_LESSONS.md`

See `docs/EXAMPLE_DEV_LESSONS.md`.
