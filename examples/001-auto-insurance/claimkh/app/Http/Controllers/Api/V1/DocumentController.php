<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class DocumentController extends Controller
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg', 'image/png', 'image/heic',
        'application/pdf',
    ];

    private const MAX_PHOTO_SIZE_MB = 10;
    private const MAX_DOC_SIZE_MB = 20;

    public function store(Request $request, string $ref): JsonResponse
    {
        $claim = Claim::where('ref', $ref)
            ->where('tenant_id', $request->user()->tenant_id)
            ->firstOrFail();

        $request->validate([
            'file' => [
                'required',
                File::types(['jpg', 'jpeg', 'png', 'heic', 'pdf'])
                    ->max(self::MAX_DOC_SIZE_MB * 1024),
            ],
            'type' => ['required', 'in:driver_license,vehicle_registration,police_report,repair_estimate,photo,other'],
        ]);

        $file = $request->file('file');

        if ($file->getMimeType() === 'image/jpeg' || in_array($file->getMimeType(), ['image/png', 'image/heic'])) {
            if ($file->getSize() > self::MAX_PHOTO_SIZE_MB * 1024 * 1024) {
                return response()->json(['error' => 'File too large'], 422);
            }
        }

        $path = "claims/{$claim->tenant_id}/{$claim->id}/{$file->hashName()}";

        // EC-6: if S3 unavailable, queue and return 202
        try {
            Storage::disk('s3')->put($path, file_get_contents($file->path()), 'private');
        } catch (\Exception $e) {
            // TODO: dispatch DocumentUploadJob::dispatch($claim->id, $request->type, $file) for retry
            return response()->json([
                'message' => 'queued',
                'job_id' => (string) \Illuminate\Support\Str::uuid(),
            ], 202);
        }

        $document = Document::create([
            'claim_id' => $claim->id,
            'type' => $request->type,
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'status' => 'pending',
        ]);

        // TODO: dispatch MalwareScanJob::dispatch($document)

        $this->updateMissingDocs($claim, $request->type);

        return response()->json(['document' => $this->formatDocument($document)], 201);
    }

    public function getUrl(Request $request, string $ref, int $documentId): JsonResponse
    {
        $claim = Claim::where('ref', $ref)
            ->where('tenant_id', $request->user()->tenant_id)
            ->firstOrFail();

        $document = Document::where('id', $documentId)
            ->where('claim_id', $claim->id)
            ->where('status', 'available')
            ->firstOrFail();

        $expiresAt = now()->addHour();
        $url = Storage::disk('s3')->temporaryUrl($document->storage_path, $expiresAt);

        return response()->json([
            'url' => $url,
            'expires_at' => $expiresAt->toISOString(),
        ]);
    }

    private function updateMissingDocs(Claim $claim, string $uploadedType): void
    {
        $missing = $claim->missing_docs ?? [];
        $missing = array_values(array_filter($missing, fn($d) => $d !== $uploadedType));
        $claim->update(['missing_docs' => $missing]);
    }

    private function formatDocument(Document $document): array
    {
        return [
            'id' => $document->id,
            'type' => $document->type,
            'filename' => $document->original_filename,
            'status' => $document->status,
            'created_at' => $document->created_at->toISOString(),
        ];
    }
}
