# Coordinate Complex Background Workflows: Safe Batching and Chaining in Laravel

Your application needs to import a catalog containing five hundred high-resolution product photos. You need to download and resize all five hundred images in parallel across multiple worker processes. Once all five hundred images finish processing, you must compile a single PDF catalog. Once the PDF compiles, you must upload it to S3 and email a download link to the merchant. If any critical image fails, you must notify the merchant without leaving orphaned files on disk.

Attempting to track multi-stage workflows using manual database flags (like an `images_processed_count` integer column) introduces race conditions, missed notifications, and brittle error recovery. Laravel's `Bus::batch()` and `Bus::chain()` primitives solve this natively. By combining parallel batches with sequential chains, you can coordinate complex background pipelines with automatic progress tracking and clean failure recovery.

## Sequential Execution: Job Chains

When tasks must execute in a strict sequential order—where Step 2 must only execute if Step 1 succeeds—use a Job Chain:

```php
use Illuminate\Support\Facades\Bus;

Bus::chain([
    new ProcessPaymentJob($order),
    new GenerateInvoicePdfJob($order),
    new SendOrderConfirmationEmailJob($order),
])->dispatch();
```

If `ProcessPaymentJob` throws an unhandled exception, Laravel halts the chain immediately. `GenerateInvoicePdfJob` and `SendOrderConfirmationEmailJob` are never dispatched, preventing you from emailing a receipt for a charge that failed.

## Parallel Execution: Job Batches

When multiple independent tasks can execute concurrently across any available worker, use a Job Batch.

Before using batches, ensure you have generated the database table that tracks batch states:

```bash
php artisan queue:batches-table
php artisan migrate
```

Now you can dispatch a collection of jobs in parallel and register completion callbacks:

app/Services/ProductCatalogImporter.php:
```php
namespace App\Services;

use App\Jobs\CompileCatalogPdfJob;
use App\Jobs\ProcessProductImageJob;
use App\Models\Merchant;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;

class ProductCatalogImporter
{
    public function import(Merchant $merchant, array $imageUrls): Batch
    {
        // Convert image URLs into individual queued jobs
        $jobs = collect($imageUrls)->map(function ($url) use ($merchant) {
            return new ProcessProductImageJob($merchant, $url);
        });

        return Bus::batch($jobs)
            ->name("catalog-import-merchant-{$merchant->id}")
            ->allowFailures()
            ->then(function (Batch $batch) use ($merchant) {
                // Executes ONLY when every single job in the batch completes successfully
                CompileCatalogPdfJob::dispatch($merchant, $batch->id);
            })
            ->catch(function (Batch $batch, Throwable $e) use ($merchant) {
                // Executes when the first job in the batch encounters a permanent failure
                $merchant->notifyCatalogImportFailed($batch->id, $e->getMessage());
            })
            ->finally(function (Batch $batch) use ($merchant) {
                // Executes when all jobs finish, regardless of success or failure
                $merchant->cleanTemporaryImportFiles($batch->id);
            })
            ->dispatch();
    }
}
```

Notice the architecture:
- Every image processes in parallel across whatever worker capacity you have available.
- `->then()` guarantees that `CompileCatalogPdfJob` only runs after all 500 images are processed and verified.
- `->allowFailures()` allows the batch to keep processing remaining images even if one image URL returns a 404, while `->catch()` alerts your team to the problem.

## Querying Batch Progress in Real-Time

Because Laravel tracks batches in the database, you can expose real-time progress to frontend progress bars:

app/Http/Controllers/BatchStatusController.php:
```php
namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Bus;

class BatchStatusController extends Controller
{
    public function show(string $batchId): JsonResponse
    {
        $batch = Bus::findBatch($batchId);

        if (! $batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        return response()->json([
            'id' => $batch->id,
            'total_jobs' => $batch->totalJobs,
            'pending_jobs' => $batch->pendingJobs,
            'failed_jobs' => $batch->failedJobs,
            'progress' => $batch->progress(), // Float percentage from 0 to 100
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled(),
        ]);
    }
}
```

Your Inertia or Vue frontend can poll `/api/batches/{id}` every two seconds to render a live progress bar showing `64% complete (320 / 500 images)` with zero custom state code.

## What Can Go Wrong

A major memory trap occurs when dispatching massive batches containing tens of thousands of items:

```php
// Dangerous: creating 100,000 job objects in memory at once exhausts RAM
$jobs = [];
for ($i = 0; $i < 100000; $i++) {
    $jobs[] = new HeavyDataJob($i);
}
Bus::batch($jobs)->dispatch();
```

Instantiating 100,000 PHP job objects in a single array before dispatching will easily exceed PHP's memory limit.

For large datasets, dispatch jobs to an existing batch incrementally inside a `chunk()` or `lazy()` loop:

```php
$batch = Bus::batch([])->dispatch();

Order::where('status', 'unpaid')->chunkById(500, function ($orders) use ($batch) {
    $jobs = $orders->map(fn ($order) => new ProcessOrderJob($order));
    $batch->add($jobs);
});
```

Using `$batch->add()` appends jobs to the running batch in manageable chunks, keeping memory flat.

## Summary

Stop building brittle, manual database counters to coordinate background pipelines.

Use `Bus::chain()` for dependent sequential steps where failure must halt the workflow. Use `Bus::batch()` for concurrent parallel processing with automated progress tracking and clean completion callbacks (`then`, `catch`, `finally`).

Combining batches and chains turns complex multi-stage data pipelines into clean, observable, and resilient background operations.

## Further Reading

- [Laravel Queues: Job Chaining](https://laravel.com/docs/queues#job-chaining)
- [Laravel Queues: Job Batching](https://laravel.com/docs/queues#job-batching)
- [Building Resilient Background Pipelines](https://martinfowler.com/articles/patterns-of-distributed-systems/)
- [Laravel Batch Table Schema Reference](https://laravel.com/docs/queues#customizing-the-batch-table)

What multi-step background workflows have you simplified using Laravel Job Batches? Tell us about your architecture in the comments below.
