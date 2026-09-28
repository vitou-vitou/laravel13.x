# Private Storage in Laravel: Stream Protected Files and Generate S3 Pre-signed URLs

A customer uploads a PDF copy of their passport or government tax return for identity verification. The developer stores the file on Laravel's default `public` disk and stores the relative path in the database. Two weeks later, a security auditor discovers that anyone who guesses or enumerates the URL format (`https://example.com/storage/passports/42.pdf`) can download confidential identity documents without logging in or passing an authorization check.

Files containing sensitive customer information, legal contracts, or medical records must never reside on a publicly accessible web root or world-readable S3 bucket. If you store sensitive assets on a private disk, stream downloads through authorized controller gates, or generate temporary, time-limited S3 pre-signed URLs, you protect your users' privacy while keeping download performance high.

## Public vs. Private Disks in Laravel

Laravel's filesystem abstraction defines distinct storage disks in `config/filesystems.php`:

- **`public` disk:** Stored in `storage/app/public` and symlinked to `public/storage`. Nginx or Apache serves these files directly as static assets. No PHP code executes when a user accesses the URL.
- **`local` / private disk:** Stored in `storage/app/private` (outside the web root). The web server refuses to serve these files directly. Access requires routing through a PHP controller that verifies authentication and authorization.

Configure your private disk explicitly in `config/filesystems.php`:

config/filesystems.php:
```php
'disks' => [
    'private_documents' => [
        'driver' => 'local',
        'root' => storage_path('app/private/documents'),
        'throw' => true,
    ],

    's3_private' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_PRIVATE_BUCKET'),
        'visibility' => 'private', // Enforces private ACLs on all uploads
        'throw' => true,
    ],
],
```

When storing sensitive files, always target the private disk:

```php
$path = $request->file('passport')->store('passports', 's3_private');
```

## Pattern 1: Authorize and Stream via Controller

For local files or smaller S3 documents where you want to audit every access, route the request through a controller protected by a Laravel Policy:

app/Http/Controllers/DocumentDownloadController.php:
```php
namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentDownloadController extends Controller
{
    public function show(Request $request, Document $document): StreamedResponse
    {
        // 1. Enforce strict authorization policy
        $this->authorize('view', $document);

        // 2. Audit the download access
        $document->recordAccessEvent($request->user());

        // 3. Stream file from private storage without loading entire file into memory
        return Storage::disk('private_documents')->response(
            $document->file_path,
            $document->original_filename,
            [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"',
            ]
        );
    }
}
```

Using `Storage::response()` streams the file directly to the client's browser using chunked transfers, ensuring that even a 200MB file uses less than 5 megabytes of PHP memory during download.

## Pattern 2: S3 Pre-Signed Temporary URLs for Large Files

If you store gigabytes of large video files, architectural blueprints, or heavy archives on AWS S3, streaming every file through your PHP web server ties up your PHP-FPM worker connections.

Instead, authorize the user in Laravel and generate a **Pre-signed Temporary S3 URL**:

app/Http/Controllers/LargeExportDownloadController.php:
```php
namespace App\Http\Controllers;

use App\Models\ExportArchive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LargeExportDownloadController extends Controller
{
    public function download(Request $request, ExportArchive $archive): RedirectResponse
    {
        // 1. Authorize the user
        $this->authorize('download', $archive);

        // 2. Generate a cryptographically signed S3 URL that expires in 15 minutes
        $temporaryUrl = Storage::disk('s3_private')->temporaryUrl(
            $archive->s3_path,
            now()->addMinutes(15),
            [
                'ResponseContentDisposition' => 'attachment; filename="' . $archive->filename . '"',
            ]
        );

        // 3. Redirect the client directly to AWS S3
        return redirect()->away($temporaryUrl);
    }
}
```

Here is why pre-signed URLs are superior for large files:
1. The user's browser downloads the file directly from AWS S3's high-speed global CDN.
2. Zero PHP-FPM worker capacity is consumed during the 5-minute file download.
3. The cryptographic signature on the URL prevents tampering, and the link permanently expires after 15 minutes.

## What Can Go Wrong

A frequent security failure occurs when generating pre-signed URLs with excessive lifespans:

```php
// Dangerous: pre-signed URL valid for 7 days can be forwarded to anyone!
$url = Storage::temporaryUrl($document->path, now()->addDays(7));
```

If an authorized user shares that 7-day link in a Slack channel or email, anyone who clicks the link can download the private file until the expiration date passes.

Keep pre-signed URL expiration windows as short as practical—between 5 and 15 minutes is ideal for interactive browser downloads.

## Summary

Never store sensitive customer documents or private business files on public storage disks.

Configure dedicated private storage disks on local storage or AWS S3. Stream sensitive files through authorized controllers with `Storage::response()` to audit access, or generate temporary pre-signed URLs with short expiration windows to offload large file transfers to cloud storage.

Your customer data remains secure, compliance requirements are met, and your web servers remain fast and responsive.

## Further Reading

- [Laravel Filesystem Documentation: File Downloads](https://laravel.com/docs/filesystem#downloading-files)
- [Amazon S3: Working with Pre-Signed URLs](https://docs.aws.amazon.com/AmazonS3/latest/userguide/using-presigned-url.html)
- [OWASP File Upload & Storage Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html)
- [PHP Stream Functions and Memory Optimization](https://www.php.net/manual/en/book.stream.php)

How does your team handle permissions and downloads for sensitive multi-gigabyte customer exports? Share your architecture in the comments below.
