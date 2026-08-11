<?php

namespace Database\Factories;

use App\Models\Export;
use App\Models\QuestionBatch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Export>
 */
class ExportFactory extends Factory
{
    protected $model = Export::class;

    public function definition(): array
    {
        return [
            'question_batch_id' => QuestionBatch::factory(),
            'user_id' => fn (array $attributes) => QuestionBatch::find($attributes['question_batch_id'])?->user_id ?? User::factory(),
            'storage_path' => null,
            'status' => 'processing',
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'storage_path' => 'exports/' . fake()->uuid() . '.pdf',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'storage_path' => null,
        ]);
    }
}
