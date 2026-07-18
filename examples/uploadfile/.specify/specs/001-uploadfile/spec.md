# Feature Specification: Upload File

**Feature Branch**: `001-uploadfile`

**Created**: 2026-07-18

**Status**: Approved — from `docs/superpowers/specs/2026-07-18-uploadfile-example-design.md` (repo root)

**Input**: Teaching slice demonstrating Laravel 13's native upload/storage vendor APIs (`UploadedFile`, FormRequest validation, `Storage`). No auth, no database.

## User Scenarios & Testing *(mandatory)*

### P1 — Upload a file

As an API consumer, I POST a multipart file to `/api/files` and receive JSON metadata about the stored file.

- Given a valid file (allowed type, ≤ 10 MB), the API stores it on the `local` disk under `uploads/` with a hashed name and returns 201 with `{filename, original_name, size, mime}`.
- Given no file, an oversized file, or a disallowed type, the API returns 422 validation JSON.

### P2 — List stored files

As an API consumer, I GET `/api/files` and receive an array of stored files with `filename`, `size`, and `last_modified`.

### P3 — Download a file

As an API consumer, I GET `/api/files/{filename}` and receive the file as a download. Unknown filenames return 404 JSON.

### P4 — Delete a file

As an API consumer, I DELETE `/api/files/{filename}` and the file is removed. Unknown filenames return 404 JSON.

## Functional Requirements

- FR-001: `POST /api/files` accepts multipart field `file`; validation `required|file|max:10240` + mime allowlist (jpg, jpeg, png, pdf, txt, csv); stores via `$file->store('uploads')`; returns 201 JSON `{filename, original_name, size, mime}`.
- FR-002: `GET /api/files` lists `Storage::files('uploads')` with size + last-modified; returns 200 JSON array.
- FR-003: `GET /api/files/{filename}` streams the file via `Storage::download()`; 404 JSON when missing.
- FR-004: `DELETE /api/files/{filename}` removes the file via `Storage::delete()`; 200 JSON on success, 404 JSON when missing.
- FR-005: `{filename}` is sanitized to its basename before any Storage call — path traversal never escapes `uploads/` and returns 404.
- FR-006: No auth; no database. `Storage::files('uploads')` is the source of truth.

## Out of Scope (MVP)

- Public disk / `storage:link`, S3, image resizing, queued processing
- Multiple-file upload, streamed/chunked large-file download
- Database metadata table, auth, UI
