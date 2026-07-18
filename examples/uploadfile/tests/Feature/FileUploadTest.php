<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    private \Illuminate\Filesystem\FilesystemAdapter $disk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disk = Storage::fake('local');
    }

    public function test_upload_stores_file_and_returns_metadata(): void
    {
        $file = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');

        $response = $this->postJson('/api/files', ['file' => $file]);

        $response->assertCreated()
            ->assertJsonStructure(['filename', 'original_name', 'size', 'mime']);

        $this->assertSame('report.pdf', $response->json('original_name'));
        $this->disk->assertExists('uploads/'.$response->json('filename'));
    }

    public function test_upload_without_file_is_rejected(): void
    {
        $this->postJson('/api/files', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_upload_over_max_size_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf');

        $this->postJson('/api/files', ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_upload_disallowed_type_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('hack.php', 10, 'text/x-php');

        $this->postJson('/api/files', ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_list_returns_stored_files_with_metadata(): void
    {
        $upload = $this->postJson('/api/files', [
            'file' => UploadedFile::fake()->create('notes.txt', 5, 'text/plain'),
        ]);

        $this->getJson('/api/files')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonStructure([['filename', 'size', 'last_modified']])
            ->assertJsonFragment(['filename' => $upload->json('filename')]);
    }

    public function test_list_is_empty_when_no_uploads(): void
    {
        $this->getJson('/api/files')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_download_returns_stored_file(): void
    {
        $upload = $this->postJson('/api/files', [
            'file' => UploadedFile::fake()->create('notes.txt', 5, 'text/plain'),
        ]);

        $this->get('/api/files/'.$upload->json('filename'))
            ->assertOk()
            ->assertDownload($upload->json('filename'));
    }

    public function test_download_unknown_file_returns_404(): void
    {
        $this->getJson('/api/files/does-not-exist.txt')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function test_delete_removes_file(): void
    {
        $upload = $this->postJson('/api/files', [
            'file' => UploadedFile::fake()->create('notes.txt', 5, 'text/plain'),
        ]);
        $filename = $upload->json('filename');

        $this->deleteJson('/api/files/'.$filename)
            ->assertOk()
            ->assertJson(['deleted' => $filename]);

        $this->disk->assertMissing('uploads/'.$filename);
    }

    public function test_delete_unknown_file_returns_404(): void
    {
        $this->deleteJson('/api/files/does-not-exist.txt')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function test_path_traversal_filename_returns_404(): void
    {
        $this->disk->put('secret.txt', 'top secret');

        $this->getJson('/api/files/..%2Fsecret.txt')
            ->assertNotFound();

        $this->disk->assertExists('secret.txt');
    }
}
