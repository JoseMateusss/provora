<?php

namespace Tests\Feature;

use App\Jobs\ExportBatchToPdf;
use App\Models\Export;
use App\Models\Question;
use App\Models\QuestionBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_initiate_export_for_question_batch(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/question-batches/{$batch->id}/export");

        $response->assertStatus(202)
            ->assertJsonStructure([
                'export_id',
                'status',
            ])
            ->assertJson([
                'status' => 'processing',
            ]);

        $exportId = $response->json('export_id');

        $this->assertDatabaseHas('exports', [
            'id' => $exportId,
            'question_batch_id' => $batch->id,
            'user_id' => $user->id,
            'status' => 'processing',
        ]);

        Queue::assertPushed(ExportBatchToPdf::class, function ($job) use ($exportId) {
            return $job->export->id === $exportId && $job->queue === 'exports';
        });
    }

    public function test_user_cannot_initiate_export_for_another_users_batch(): void
    {
        Queue::fake();

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/v1/question-batches/{$batch->id}/export");

        $response->assertStatus(403);
        Queue::assertNothingPushed();
    }

    public function test_user_can_view_export_status_when_processing(): void
    {
        $user = User::factory()->create();
        $export = Export::factory()->create([
            'user_id' => $user->id,
            'status' => 'processing',
            'storage_path' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/exports/{$export->id}");

        $response->assertStatus(200)
            ->assertJson([
                'export_id' => $export->id,
                'status' => 'processing',
            ])
            ->assertJsonMissingPath('download_url');
    }

    public function test_user_can_view_export_status_and_download_url_when_completed(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create();
        $export = Export::factory()->completed()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/exports/{$export->id}");

        $response->assertStatus(200)
            ->assertJson([
                'export_id' => $export->id,
                'status' => 'completed',
            ])
            ->assertJsonStructure([
                'export_id',
                'status',
                'download_url',
            ]);
    }

    public function test_user_cannot_view_another_users_export(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $export = Export::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->getJson("/api/v1/exports/{$export->id}");

        $response->assertStatus(403);
    }

    public function test_job_renders_pdf_and_updates_export_status(): void
    {
        Pdf::fake();

        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);

        // Question 1 & 2 active
        $q1 = Question::factory()->create(['question_batch_id' => $batch->id, 'user_id' => $user->id, 'status' => 'draft', 'order' => 1]);
        $q2 = Question::factory()->create(['question_batch_id' => $batch->id, 'user_id' => $user->id, 'status' => 'approved', 'order' => 2]);

        // Question 3 deleted (should be ignored)
        $q3 = Question::factory()->create(['question_batch_id' => $batch->id, 'user_id' => $user->id, 'status' => 'deleted', 'order' => 3]);

        $export = Export::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $user->id,
            'status' => 'processing',
        ]);

        $job = new ExportBatchToPdf($export);
        $job->handle();

        $export->refresh();

        $this->assertEquals('completed', $export->status);
        $this->assertEquals('exports/' . $export->id . '.pdf', $export->storage_path);

        Pdf::assertSaved('exports/' . $export->id . '.pdf');
    }

    public function test_job_handles_failure_and_updates_status_to_failed(): void
    {
        Pdf::shouldReceive('view')
            ->andThrow(new \RuntimeException('PDF generation failed'));

        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);

        $export = Export::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $user->id,
            'status' => 'processing',
        ]);

        $job = new ExportBatchToPdf($export);

        try {
            $job->handle();
        } catch (\Throwable $e) {
            // Expected exception
        }

        $export->refresh();
        $this->assertEquals('failed', $export->status);
    }
}
