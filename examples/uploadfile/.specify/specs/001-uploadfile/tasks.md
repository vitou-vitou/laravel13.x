# Tasks: 001-uploadfile

- [x] T001 Complete spec.md (from approved design doc)
- [x] T002 Complete plan.md
- [x] T003 Write feature tests (red) — `tests/Feature/FileUploadTest.php`, `Storage::fake('local')`:
  - upload happy path → 201 + file exists on disk
  - upload no file → 422
  - upload over 10 MB → 422
  - upload disallowed mime → 422
  - list returns uploaded files with metadata
  - download returns stored file
  - download unknown filename → 404
  - delete removes file → 200 + gone from disk
  - delete unknown filename → 404
  - path-traversal filename → 404
- [x] T004 Implement (green) — `StoreFileRequest`, `FileController`, `routes/api.php` wiring
- [x] T005 README (jwt-style curl examples) + `php artisan test` green + `verify-example uploadfile` + update docs/NEXT_SESSION.md + SESSION_STATE.md
