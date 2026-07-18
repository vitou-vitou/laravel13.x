<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    private const DIR = 'uploads';

    public function store(StoreFileRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $path = $file->store(self::DIR);

        return response()->json([
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getClientMimeType(),
        ], 201);
    }

    public function index(): JsonResponse
    {
        $disk = Storage::disk('local');

        $files = collect($disk->files(self::DIR))->map(fn (string $path) => [
            'filename' => basename($path),
            'size' => $disk->size($path),
            'last_modified' => $disk->lastModified($path),
        ])->values();

        return response()->json($files);
    }

    public function download(string $filename): StreamedResponse
    {
        return Storage::disk('local')->download($this->path($filename));
    }

    public function destroy(string $filename): JsonResponse
    {
        $path = $this->path($filename);

        Storage::disk('local')->delete($path);

        return response()->json(['deleted' => basename($path)]);
    }

    /**
     * Resolve a client-supplied filename to a safe path inside uploads/,
     * aborting 404 when the file does not exist or the name is unsafe.
     */
    private function path(string $filename): string
    {
        $safe = basename($filename);
        $path = self::DIR.'/'.$safe;

        if ($safe !== $filename || ! Storage::disk('local')->exists($path)) {
            abort(404, 'File not found.');
        }

        return $path;
    }
}
