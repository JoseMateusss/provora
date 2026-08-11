<?php

namespace Tests\Feature;

use App\Jobs\GenerateQuestionsJob;
use App\Models\Document;
use App\Models\QuestionBatch;
use App\Models\User;
use App\Services\Ai\Contracts\LlmProviderInterface;
use App\Services\Ai\DTOs\GeneratedQuestionsResult;
use App\Services\Ai\QuestionValidatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class QuestionBatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_question_batch_successfully(): void
    {
        Queue::fake();

        $user = User::factory()->create(['plan' => 'free', 'questions_generated_this_month' => 0]);
        $document = Document::factory()->create([
            'user_id' => $user->id,
            'status' => 'extracted',
            'extracted_text' => 'Texto sobre fotossíntese e respiração celular.',
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/question-batches', [
            'document_id' => $document->id,
            'knowledge_area' => 'natureza',
            'difficulty' => 'medio',
            'requested_count' => 5,
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure(['id', 'status', 'requested_count'])
            ->assertJson([
                'status' => 'processing',
                'requested_count' => 5,
            ]);

        $this->assertDatabaseHas('question_batches', [
            'user_id' => $user->id,
            'document_id' => $document->id,
            'knowledge_area' => 'natureza',
            'difficulty' => 'medio',
            'requested_count' => 5,
            'status' => 'processing',
        ]);

        Queue::assertPushed(GenerateQuestionsJob::class);
    }

    public function test_creation_fails_when_document_does_not_belong_to_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $document = Document::factory()->create([
            'user_id' => $otherUser->id,
            'status' => 'extracted',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/question-batches', [
            'document_id' => $document->id,
            'knowledge_area' => 'natureza',
            'difficulty' => 'medio',
            'requested_count' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['details' => ['document_id']]]);
    }

    public function test_creation_fails_when_document_is_not_extracted(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'extracted_text' => null,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/question-batches', [
            'document_id' => $document->id,
            'knowledge_area' => 'natureza',
            'difficulty' => 'medio',
            'requested_count' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['details' => ['document_id']]]);
    }

    public function test_creation_fails_when_user_exceeds_plan_limit(): void
    {
        $user = User::factory()->create([
            'plan' => 'free', // Limite de 10 questões
            'questions_generated_this_month' => 8,
        ]);

        $document = Document::factory()->create([
            'user_id' => $user->id,
            'status' => 'extracted',
        ]);

        // Solicita 5 (totalizaria 13 > 10)
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/question-batches', [
            'document_id' => $document->id,
            'knowledge_area' => 'natureza',
            'difficulty' => 'medio',
            'requested_count' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['details' => ['requested_count']]]);
    }

    public function test_creation_fails_with_invalid_requested_count(): void
    {
        $user = User::factory()->create();
        $document = Document::factory()->create([
            'user_id' => $user->id,
            'status' => 'extracted',
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/question-batches', [
            'document_id' => $document->id,
            'knowledge_area' => 'natureza',
            'difficulty' => 'medio',
            'requested_count' => 25, // Maior que 20
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['details' => ['requested_count']]]);
    }

    public function test_job_generates_questions_and_updates_batch_status_to_completed(): void
    {
        $user = User::factory()->create(['questions_generated_this_month' => 0]);
        $document = Document::factory()->create([
            'user_id' => $user->id,
            'status' => 'extracted',
            'extracted_text' => 'Texto sobre celulas vegetais e fotossintese.',
        ]);

        $batch = QuestionBatch::factory()->create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'knowledge_area' => 'natureza',
            'difficulty' => 'medio',
            'requested_count' => 1,
            'status' => 'processing',
        ]);

        $mockLlm = $this->createMock(LlmProviderInterface::class);
        $mockLlm->expects($this->once())
            ->method('generateQuestions')
            ->willReturn(new GeneratedQuestionsResult(
                questions: [
                    [
                        'statement' => 'Qual a função dos cloroplastos?',
                        'alternatives' => [
                            ['letter' => 'A', 'text' => 'Realizar a fotossíntese.'],
                            ['letter' => 'B', 'text' => 'Sintetizar lipídios.'],
                            ['letter' => 'C', 'text' => 'Digerir resíduos.'],
                            ['letter' => 'D', 'text' => 'Armazenar DNA nuclear.'],
                            ['letter' => 'E', 'text' => 'Produzir hemoglobina.'],
                        ],
                        'correct_alternative' => 'A',
                        'explanation' => 'Cloroplastos contêm clorofila para síntese de glicose.',
                        'difficulty' => 'medio',
                    ],
                ]
            ));

        $job = new GenerateQuestionsJob($batch);
        $job->handle($mockLlm, new QuestionValidatorService());

        $batch->refresh();

        $this->assertEquals('completed', $batch->status);
        $this->assertEquals(1, $batch->generated_count);
        $this->assertCount(1, $batch->questions);
        $this->assertEquals(1, $user->fresh()->questions_generated_this_month);
    }

    public function test_job_handles_total_failure_without_charging_user(): void
    {
        $user = User::factory()->create(['questions_generated_this_month' => 0]);
        $document = Document::factory()->create([
            'user_id' => $user->id,
            'status' => 'extracted',
            'extracted_text' => 'Texto sobre células.',
        ]);

        $batch = QuestionBatch::factory()->create([
            'user_id' => $user->id,
            'document_id' => $document->id,
            'knowledge_area' => 'natureza',
            'difficulty' => 'medio',
            'requested_count' => 2,
            'status' => 'processing',
        ]);

        // Retorna questões totalmente malformadas
        $mockLlm = $this->createMock(LlmProviderInterface::class);
        $mockLlm->expects($this->once())
            ->method('generateQuestions')
            ->willReturn(new GeneratedQuestionsResult(
                questions: [
                    ['invalid' => 'data'],
                ]
            ));

        $job = new GenerateQuestionsJob($batch);
        $job->handle($mockLlm, new QuestionValidatorService());

        $batch->refresh();

        $this->assertEquals('failed', $batch->status);
        $this->assertEquals(0, $batch->generated_count);
        $this->assertNotNull($batch->error_message);
        // Uso NÃO deve ter sido cobrado
        $this->assertEquals(0, $user->fresh()->questions_generated_this_month);
    }

    public function test_user_can_view_their_question_batch(): void
    {
        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson("/api/v1/question-batches/{$batch->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $batch->id,
                'status' => $batch->status,
                'requested_count' => $batch->requested_count,
            ]);
    }

    public function test_user_cannot_view_another_users_question_batch(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->getJson("/api/v1/question-batches/{$batch->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_list_their_question_batches(): void
    {
        $user = User::factory()->create();
        QuestionBatch::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/v1/question-batches');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_delete_their_question_batch(): void
    {
        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/question-batches/{$batch->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('question_batches', ['id' => $batch->id]);
    }
}
