<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\QuestionBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_their_question(): void
    {
        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);
        $question = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/questions/{$question->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $question->id,
                'question_batch_id' => $batch->id,
                'statement' => $question->statement,
                'status' => 'draft',
            ])
            ->assertJsonStructure([
                'id',
                'question_batch_id',
                'statement',
                'alternatives',
                'correct_alternative',
                'explanation',
                'difficulty',
                'status',
                'order',
            ]);
    }

    public function test_user_cannot_view_another_users_question(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $otherUser->id]);
        $question = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/questions/{$question->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_partially_update_question_statement(): void
    {
        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);
        $question = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $user->id,
            'statement' => 'Enunciado original',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->patchJson("/api/v1/questions/{$question->id}", [
            'statement' => 'Enunciado revisado e melhorado',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $question->id,
                'statement' => 'Enunciado revisado e melhorado',
                'status' => 'edited',
            ]);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'statement' => 'Enunciado revisado e melhorado',
            'status' => 'edited',
        ]);
    }

    public function test_user_can_update_alternatives_and_correct_alternative(): void
    {
        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);
        $question = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $user->id,
            'correct_alternative' => 'A',
            'status' => 'draft',
        ]);

        $newAlternatives = [
            ['letter' => 'A', 'text' => 'Opção A nova'],
            ['letter' => 'B', 'text' => 'Opção B nova'],
            ['letter' => 'C', 'text' => 'Opção C nova'],
            ['letter' => 'D', 'text' => 'Opção D nova'],
            ['letter' => 'E', 'text' => 'Opção E nova'],
        ];

        $response = $this->actingAs($user)->patchJson("/api/v1/questions/{$question->id}", [
            'alternatives' => $newAlternatives,
            'correct_alternative' => 'C',
            'explanation' => 'Gabarito é C devido ao motivo X.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $question->id,
                'correct_alternative' => 'C',
                'explanation' => 'Gabarito é C devido ao motivo X.',
                'status' => 'edited',
            ]);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'correct_alternative' => 'C',
            'status' => 'edited',
        ]);
    }

    public function test_update_fails_when_alternatives_do_not_have_five_valid_letters(): void
    {
        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);
        $question = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $user->id,
        ]);

        // Apenas 4 alternativas
        $response = $this->actingAs($user)->patchJson("/api/v1/questions/{$question->id}", [
            'alternatives' => [
                ['letter' => 'A', 'text' => 'Texto A'],
                ['letter' => 'B', 'text' => 'Texto B'],
                ['letter' => 'C', 'text' => 'Texto C'],
                ['letter' => 'D', 'text' => 'Texto D'],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['details' => ['alternatives']]]);
    }

    public function test_update_fails_when_correct_alternative_is_invalid(): void
    {
        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);
        $question = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->patchJson("/api/v1/questions/{$question->id}", [
            'correct_alternative' => 'Z',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['error' => ['details' => ['correct_alternative']]]);
    }

    public function test_user_cannot_update_another_users_question(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $otherUser->id]);
        $question = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->patchJson("/api/v1/questions/{$question->id}", [
            'statement' => 'Tentativa de alteração não autorizada',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_soft_delete_question(): void
    {
        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);
        $question = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $user->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/questions/{$question->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $question->id,
                'status' => 'deleted',
            ]);

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'status' => 'deleted',
        ]);
    }

    public function test_deleted_question_is_excluded_from_active_questions_relation(): void
    {
        $user = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $user->id]);

        $activeQuestion = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'status' => 'draft',
        ]);

        $deletedQuestion = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'status' => 'deleted',
        ]);

        $this->assertCount(2, $batch->questions);
        $this->assertCount(1, $batch->activeQuestions);
        $this->assertEquals($activeQuestion->id, $batch->activeQuestions->first()->id);
    }

    public function test_user_cannot_delete_another_users_question(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $batch = QuestionBatch::factory()->create(['user_id' => $otherUser->id]);
        $question = Question::factory()->create([
            'question_batch_id' => $batch->id,
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/questions/{$question->id}");

        $response->assertStatus(403);
    }
}
