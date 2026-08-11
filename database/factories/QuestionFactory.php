<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'question_batch_id' => QuestionBatch::factory(),
            'user_id' => function (array $attributes) {
                return QuestionBatch::find($attributes['question_batch_id'] ?? null)?->user_id ?? User::factory();
            },
            'statement' => $this->faker->paragraph(),
            'alternatives' => [
                ['letter' => 'A', 'text' => $this->faker->sentence()],
                ['letter' => 'B', 'text' => $this->faker->sentence()],
                ['letter' => 'C', 'text' => $this->faker->sentence()],
                ['letter' => 'D', 'text' => $this->faker->sentence()],
                ['letter' => 'E', 'text' => $this->faker->sentence()],
            ],
            'correct_alternative' => 'A',
            'explanation' => $this->faker->paragraph(),
            'difficulty' => 'medio',
            'status' => 'draft',
            'order' => 1,
        ];
    }
}
