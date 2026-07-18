# uploadFile example — design

**Date:** 2026-07-18
**Status:** Approved (brainstorming complete)
**Location:** `examples/uploadFile/`
**Type:** Teaching slice (like `examples/jwt`) — demonstrates Laravel 13's native upload/storage vendor APIs

## Goal

A small, standalone Laravel 13 API that teaches the core file-upload patterns from the framework itself: `UploadedFile`, FormRequest validation, `Storage::disk()`, `Storage::download()`, `Storage::delete()`. No auth, no database — the whole slice runs on the filesystem.

## Locked decisions

| Decision | Choice |
|----------|--------|
| Purpose | Teaching slice with curl-driven README (jwt-style) |
| Auth | None — auth is taught by `examples/jwt` / `examples/passport` |
| Scope | Core four: upload, list, download, delete |
| Persistence | Filesystem-only — no model, no migration; `Storage::files('uploads')` is the source of truth |
| Disk | `local` (private), `uploads/` directory, hashed filenames via `$file->store('uploads')` |
| Structure | Single `FileController` (`store`, `index`, `download`, `destroy`) + `StoreFileRequest` |
| SDD | Spec-Kit + Superpowers TDD (greenfield policy — no OpenSpec at init) |
| Scaffold | `./bin/new-example uploadFile` → Herd link, Spec-Kit stubs, `verify-example` must pass |

## Endpoints

Routes in `routes/api.php` under `/api/files`:

| Method | Endpoint | Description | Success | Errors |
|--------|----------|-------------|---------|--------|
| POST | `/api/files` | Multipart upload (`file` field) | 201 — `{filename, original_name, size, mime}` | 422 validation JSON |
| GET | `/api/files` | List stored files with size + last-modified | 200 — array | — |
| GET | `/api/files/{filename}` | Download via `Storage::download()` | 200 stream | 404 JSON if missing |
| DELETE | `/api/files/{filename}` | Delete via `Storage::delete()` | 200 JSON | 404 JSON if missing |

## Validation & safety

- `StoreFileRequest`: `file` is `required|file|max:10240` (10 MB) with a mime allowlist (e.g. `jpg,jpeg,png,pdf,txt,csv`).
- `{filename}` route parameter is sanitized to its basename before any Storage call — path traversal (`../`) returns 404, never touches files outside `uploads/`.
- Original client filenames are returned in the upload response but not persisted (filesystem-only trade-off, accepted).
- README notes `Accept: application/json` requirement, same as the jwt example.

## Testing (TDD — tests first)

Feature tests using `Storage::fake('local')`, ~8–10 cases:

1. Upload happy path → 201, file exists on fake disk
2. Upload with no file → 422
3. Upload over max size → 422
4. Upload disallowed mime → 422
5. List returns uploaded files with metadata
6. Download returns the stored file
7. Download unknown filename → 404
8. Delete removes file → 200; file gone from disk
9. Delete unknown filename → 404
10. Path-traversal filename (`..%2F..%2F.env`) → 404

## README (jwt-style)

Setup (`composer install`, `cp .env.example .env`, `key:generate`), endpoint table, `curl.exe` examples for all four endpoints (Windows PowerShell alias caveat), notes section.

## Out of scope (YAGNI)

- Public disk / `storage:link`, S3, image resizing, queued processing
- Multiple-file upload, streamed/chunked large-file download
- Database metadata table, auth, UI

## Implementation flow

1. `export PATH="/d/laravel13.x/bin:$PATH"` → `./bin/new-example uploadFile`
2. `./bin/verify-example uploadFile` passes
3. Spec-Kit: fill `.specify/specs/001-uploadfile/` spec → plan → tasks from this design
4. Superpowers TDD: write the feature tests above, then `StoreFileRequest`, `FileController`, routes
5. `php artisan test` green → README → `verify-example` again → update `SESSION_STATE.md` / `NEXT_SESSION.md`
