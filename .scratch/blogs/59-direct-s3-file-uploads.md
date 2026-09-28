# Upload Giant Files Directly to S3: Bypass Web Server Memory Limits in Laravel

A user attempts to upload a 500-megabyte video file or high-resolution architectural scan through your standard Laravel form. Ten seconds into the upload, the browser returns `413 Request Entity Too Large` from Nginx, or PHP crashes with an unhandled fatal error: `Maximum execution time of 30 seconds exceeded`. You try bumping `upload_max_filesize`, `post_max_size`, and `memory_limit` in `php.ini`, but under traffic spikes three concurrent 500MB uploads saturate your server's RAM and tie up your PHP-FPM worker connections.

Routing multi-hundred-megabyte file uploads through your application web servers is an architectural anti-pattern. Web servers should handle lightweight business logic, not proxy massive binary file streams. By generating signed AWS S3 pre-signed upload URLs and uploading directly from the user's browser, you completely bypass your web server's memory, CPU, and execution timeout limits.

## The Architecture Flaw of Web-Server Upload Proxying

When an application handles file uploads through standard controllers:

```
[User Browser] ---> (500MB Upload) ---> [Nginx Server] ---> [PHP-FPM Worker] ---> [AWS S3]
```

This creates severe bottlenecks:
1. **Memory & I/O Saturation:** The entire binary payload streams into temporary disk space on your web server before PHP streams it a second time to cloud storage.
2. **Worker Starvation:** A slow cellular connection uploading 500MB holds a PHP-FPM worker connection hostage for several minutes. Just ten concurrent uploads will completely exhaust your web server's worker pool.
3. **Double Bandwidth Costs:** You pay for outbound server bandwidth twice: once into your server and once from your server to S3.

## The Direct-to-S3 Architecture

Direct uploads invert the workflow:

```
1. [User Browser] --- "Give me an upload URL for file.mp4" ---> [Laravel Controller] (Validates & Authorizes)
2. [User Browser] <--- Returns Pre-signed S3 PUT URL (200ms) <--- [Laravel Controller]
3. [User Browser] ================== (Uploads 500MB Directly to S3) ==================> [AWS S3]
4. [User Browser] --- "Upload completed at /videos/1042.mp4" ---> [Laravel Controller] (Saves metadata)
```

Your Laravel application never touches a single byte of the 500MB file. PHP only executes two fast, five-millisecond JSON requests to authorize the upload and record the final database record.

## Step 1: Configure S3 Bucket CORS

To allow web browsers to upload directly to your AWS S3 bucket from your domain, configure Cross-Origin Resource Sharing (CORS) on your bucket in the AWS Console:

s3-cors-policy.json:
```json
[
  {
    "AllowedHeaders": ["*"],
    "AllowedMethods": ["PUT", "POST"],
    "AllowedOrigins": ["https://app.example.com"],
    "ExposeHeaders": ["ETag"],
    "MaxAgeSeconds": 3000
  }
]
```

This permits browsers on `app.example.com` to send HTTP `PUT` requests directly to S3.

## Step 2: Generate Pre-Signed Upload URLs in Laravel

Create a controller action that authorizes the user and generates a signed PUT URL that expires in ten minutes:

app/Http/Controllers/DirectUploadController.php:
```php
namespace App\Http\Controllers;

use App\Models\Video;
use Aws\S3\S3Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DirectUploadController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'filename' => ['required', 'string'],
            'content_type' => ['required', 'string', 'in:video/mp4,video/quicktime,application/pdf'],
            'file_size' => ['required', 'integer', 'max:1073741824'], // 1GB max limit
        ]);

        $user = $request->user();

        // 1. Generate an unpredictable, safe storage key
        $extension = pathinfo($request->filename, PATHINFO_EXTENSION);
        $s3Key = "uploads/{$user->id}/" . Str::uuid() . ".{$extension}";

        // 2. Instantiate AWS S3 Client from Laravel's storage driver
        /** @var S3Client $s3Client */
        $s3Client = app('filesystem')->disk('s3')->getClient();
        $bucket = config('filesystems.disks.s3.bucket');

        // 3. Create a pre-signed PUT command
        $cmd = $s3Client->getCommand('PutObject', [
            'Bucket' => $bucket,
            'Key' => $s3Key,
            'ContentType' => $request->content_type,
            'ACL' => 'private',
        ]);

        // Generate signed URL valid for 15 minutes
        $preSignedRequest = $s3Client->createPresignedRequest($cmd, '+15 minutes');
        $uploadUrl = (string) $preSignedRequest->getUri();

        return response()->json([
            'upload_url' => $uploadUrl,
            's3_key' => $s3Key,
        ]);
    }
}
```

The pre-signed URL contains a temporary AWS signature authorizing only this specific file path, content type, and expiration window.

## Step 3: Frontend Direct Upload with Progress Tracking

On the frontend, use Axios or Fetch to send the file directly to S3 with a native progress bar:

resources/js/Components/DirectUploader.vue:
```vue
<script setup>
import { ref } from 'vue';
import axios from 'axios';

const uploadProgress = ref(0);
const isUploading = ref(false);

async function handleFileSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    isUploading.value = true;
    uploadProgress.value = 0;

    try {
        // 1. Request signed URL from Laravel
        const { data } = await axios.post('/api/uploads/signed-url', {
            filename: file.name,
            content_type: file.type,
            file_size: file.size,
        });

        // 2. Upload file DIRECTLY to S3 using native HTTP PUT
        await axios.put(data.upload_url, file, {
            headers: {
                'Content-Type': file.type,
            },
            onUploadProgress: (progressEvent) => {
                uploadProgress.value = Math.round(
                    (progressEvent.loaded * 100) / progressEvent.total
                );
            },
        });

        // 3. Notify Laravel that the upload finished
        await axios.post('/api/videos', {
            title: file.name,
            s3_key: data.s3_key,
        });

        alert('Upload completed successfully!');
    } catch (error) {
        alert('Upload failed: ' + (error.response?.data?.message || error.message));
    } finally {
        isUploading.value = false;
    }
}
</script>

<template>
    <div class="p-4 border rounded max-w-md">
        <label class="block font-medium mb-2">Upload Heavy Video (Direct to S3)</label>
        <input type="file" @change="handleFileSelect" :disabled="isUploading" />

        <div v-if="isUploading" class="mt-4">
            <div class="w-full bg-gray-200 rounded h-2.5">
                <div class="bg-blue-600 h-2.5 rounded" :style="{ width: `${uploadProgress}%` }"></div>
            </div>
            <p class="text-xs text-gray-500 mt-1">{{ uploadProgress }}% uploaded</p>
        </div>
    </div>
</template>
```

The browser streams the 500MB file directly to AWS's global network infrastructure. The progress bar updates smoothly, and your web server's CPU and RAM remain completely unaffected.

## What Can Go Wrong

A frequent trap is failing to clean up **orphaned uploads**.

If a user generates a pre-signed URL, uploads 500MB to S3, but closes their laptop before Step 3 (notifying Laravel to save the database record), that 500MB file will sit on your S3 bucket forever, accruing storage charges.

To prevent orphaned storage costs, configure an **S3 Lifecycle Rule** on your bucket:
- Automatically delete files under the `uploads/` prefix that have not been tagged or associated with a database record within 24 hours.

## Summary

Stop letting giant file uploads overwhelm your Laravel application servers.

Adopt direct-to-S3 uploads. Let Laravel authenticate requests and generate temporary pre-signed S3 PUT URLs, stream files directly from the user's browser to cloud storage, and notify Laravel upon completion.

Your web servers remain fast and responsive, execution timeout errors disappear, and your application can comfortably handle files of any size.

## Further Reading

- [AWS S3 Documentation: Uploading Objects Using Pre-Signed URLs](https://docs.aws.amazon.com/AmazonS3/latest/userguide/PresignedUrlUploadObject.html)
- [Laravel Filesystem S3 Integration](https://laravel.com/docs/filesystem#amazon-s3-compatible-filesystems)
- [Axios onUploadProgress Documentation](https://axios-http.com/docs/req_config)
- [Amazon S3 Lifecycle Configuration Rules](https://docs.aws.amazon.com/AmazonS3/latest/userguide/object-lifecycle-mgmt.html)

What is the largest file size your users upload directly to S3? Tell us about your media architecture in the comments below.
