<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\QuestionBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuestionBatch>
 */
class QuestionBatchFactory extends Factory
{
    protected $model = QuestionBatch::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'document_id' => Document::factory()->extracted(),
            'knowledge_area' => $this->faker->randomElement(['linguagens', 'humanas', 'natureza', 'matematica']),
            'difficulty' => $this->faker->randomElement(['facil', 'medio', 'dificil']),
            'requested_count' => $this->faker->numberBetween(1, 10),
            'generated_count' => 0,
            'status' => 'processing',
            'error_message' => null,
        ];
    }
}
