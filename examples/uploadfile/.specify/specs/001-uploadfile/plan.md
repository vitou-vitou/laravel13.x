# Implementation Plan: Upload File

**Branch**: `001-uploadfile` | **Date**: 2026-07-18

## Summary

Four JSON endpoints under `/api/files` teaching Laravel's vendor upload/storage APIs: upload (validated), list, download, delete. Filesystem-only (`local` disk, `uploads/` dir), no auth, no DB.

## Technical Context

- PHP 8.4, Laravel 13.x (framework v13.20)
- Routes: `routes/api.php` (register via `bootstrap/app.php` `withRouting(api: ...)`)
- Controller: `app/Http/Controllers/FileController.php` — `store`, `index`, `download`, `destroy`
- Validation: `app/Http/Requests/StoreFileRequest.php` — `required|file|max:10240|mimes:jpg,jpeg,png,pdf,txt,csv`
- Safety: basename() the `{filename}` route param before any `Storage` call
- Tests: `tests/Feature/FileUploadTest.php` with `Storage::fake('local')` — ~10 cases (happy path, 3 validation failures, list, download, delete, two 404s, path traversal)
- Herd: `http://uploadfile.test` — `npm run dev` runs Vite only

## Structure

| File | Purpose |
|------|---------|
| `routes/api.php` | `POST/GET /api/files`, `GET/DELETE /api/files/{filename}` |
| `app/Http/Controllers/FileController.php` | 4 actions, all through `Storage::disk('local')` |
| `app/Http/Requests/StoreFileRequest.php` | upload validation rules |
| `tests/Feature/FileUploadTest.php` | TDD suite, `Storage::fake` + `UploadedFile::fake` |
| `README.md` | jwt-style: setup, endpoint table, curl.exe examples |
