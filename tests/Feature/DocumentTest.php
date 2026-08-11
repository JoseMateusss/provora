<?php

namespace Tests\Feature;

use App\Jobs\ExtractTextFromDocument;
use App\Models\Document;
use App\Models\User;
use App\Services\Documents\PdfExtractorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_pdf_document_successfully(): void
    {
        Storage::fake('local');
        Queue::fake();

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('apostila-quimica.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
            ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'id',
                'original_filename',
                'status',
            ])
            ->assertJson([
                'original_filename' => 'apostila-quimica.pdf',
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'original_filename' => 'apostila-quimica.pdf',
            'status' => 'pending',
        ]);

        $document = Document::first();
        Storage::disk('local')->assertExists($document->storage_path);

        Queue::assertPushed(ExtractTextFromDocument::class, function ($job) use ($document) {
            return $job->document->id === $document->id;
        });
    }

    public function test_upload_fails_when_file_is_not_a_pdf(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('relatorio.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error' => [
                    'code',
                    'message',
                    'details' => [
                        'file',
                    ],
                ],
            ]);
    }

    public function test_upload_fails_when_file_exceeds_20mb_limit(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        // 25 MB = 25600 KB
        $file = UploadedFile::fake()->create('grande.pdf', 25600, 'application/pdf');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/documents', [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error' => [
                    'code',
                    'message',
                    'details' => [
                        'file',
                    ],
                ],
            ]);
    }

    public function test_job_extracts_text_and_updates_document_status(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/test.pdf', 'dummy content');

        $document = Document::factory()->create([
            'storage_path' => 'documents/test.pdf',
            'status' => 'pending',
        ]);

        $mockExtractor = Mockery::mock(PdfExtractorService::class);
        $mockExtractor->shouldReceive('extract')
            ->once()
            ->andReturn('Texto extraído com sucesso do PDF.');

        $job = new ExtractTextFromDocument($document);
        $job->handle($mockExtractor);

        $document->refresh();

        $this->assertEquals('extracted', $document->status);
        $this->assertEquals('Texto extraído com sucesso do PDF.', $document->extracted_text);
        $this->assertNull($document->error_message);
    }

    public function test_job_marks_document_as_failed_on_extraction_error(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/corrupt.pdf', 'corrupt content');

        $document = Document::factory()->create([
            'storage_path' => 'documents/corrupt.pdf',
            'status' => 'pending',
        ]);

        $mockExtractor = Mockery::mock(PdfExtractorService::class);
        $mockExtractor->shouldReceive('extract')
            ->once()
            ->andThrow(new \Exception('Arquivo PDF corrompido.'));

        $job = new ExtractTextFromDocument($document);
        $job->handle($mockExtractor);

        $document->refresh();

        $this->assertEquals('failed', $document->status);
        $this->assertEquals('Arquivo PDF corrompido.', $document->error_message);
    }

    public function test_user_can_view_their_document_with_text_preview(): void
    {
        $user = User::factory()->create();
        $longText = str_repeat('A', 600);

        $document = Document::factory()->create([
            'user_id' => $user->id,
            'status' => 'extracted',
            'extracted_text' => $longText,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $document->id,
                'status' => 'extracted',
                'original_filename' => $document->original_filename,
                'text_preview' => str_repeat('A', 500),
            ]);
    }

    public function test_user_can_list_their_documents(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Document::factory()->count(3)->create(['user_id' => $user->id]);
        Document::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/documents');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_cannot_view_another_users_document(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $document = Document::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_delete_their_document(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        Storage::disk('local')->put('documents/sample.pdf', 'pdf data');

        $document = Document::factory()->create([
            'user_id' => $user->id,
            'storage_path' => 'documents/sample.pdf',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('documents', [
            'id' => $document->id,
        ]);

        Storage::disk('local')->assertMissing('documents/sample.pdf');
    }

    public function test_user_cannot_delete_another_users_document(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $document = Document::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(403);
    }
}
