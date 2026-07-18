# Laravel 13 File Upload Example

Teaching slice for Laravel's native upload/storage APIs: `UploadedFile`, FormRequest validation, `Storage::disk()`, `Storage::download()`, `Storage::delete()`. No auth, no database — the `local` disk's `uploads/` directory is the source of truth.

## Requirements

- PHP 8.3+
- Composer

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Served via Herd at `http://uploadfile.test` (or `php artisan serve --port=8001`).

## API Endpoints

| Method | Endpoint | Description | Errors |
|--------|----------|-------------|--------|
| POST | `/api/files` | Upload a file (multipart field `file`) | 422 validation |
| GET | `/api/files` | List stored files | — |
| GET | `/api/files/{filename}` | Download a file | 404 |
| DELETE | `/api/files/{filename}` | Delete a file | 404 |

**Validation:** `required|file|max:10240` (10 MB) with mime allowlist `jpg,jpeg,png,pdf,txt,csv`. Files are stored with hashed names; the original name is returned in the upload response but not persisted.

**Safety:** `{filename}` is reduced to its basename before any `Storage` call — path traversal (`../`) returns 404.

## Usage

> **Windows:** Use `curl.exe` (not `curl`) to avoid PowerShell alias conflict.

### Upload

```bash
curl.exe -s -X POST http://uploadfile.test/api/files \
  -H "Accept: application/json" \
  -F "file=@./report.pdf"
```

```json
{
  "filename": "aB3xY9....pdf",
  "original_name": "report.pdf",
  "size": 102400,
  "mime": "application/pdf"
}
```

### List

```bash
curl.exe -s http://uploadfile.test/api/files -H "Accept: application/json"
```

```json
[
  { "filename": "aB3xY9....pdf", "size": 102400, "last_modified": 1789623000 }
]
```

### Download

```bash
curl.exe -s -o report.pdf http://uploadfile.test/api/files/aB3xY9....pdf
```

### Delete

```bash
curl.exe -s -X DELETE http://uploadfile.test/api/files/aB3xY9....pdf \
  -H "Accept: application/json"
```

```json
{ "deleted": "aB3xY9....pdf" }
```

## Tests

```bash
php artisan test
```

Feature tests use `Storage::fake('local')` — upload happy path, validation failures (missing / oversized / disallowed type), list, download, delete, 404s, and path-traversal rejection.

## Notes

- Always include `Accept: application/json` — errors return HTML otherwise.
- Vendor APIs demonstrated: `$file->store()`, `Storage::files()`, `Storage::download()`, `Storage::delete()`, `Storage::size()`, `Storage::lastModified()`.
