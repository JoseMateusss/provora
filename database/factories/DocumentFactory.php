<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'original_filename' => $this->faker->word() . '.pdf',
            'storage_path' => 'documents/' . $this->faker->uuid() . '.pdf',
            'status' => 'pending',
            'extracted_text' => null,
            'error_message' => null,
        ];
    }

    public function extracted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'extracted',
            'extracted_text' => $this->faker->paragraphs(5, true),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => 'Falha ao processar arquivo PDF.',
        ]);
    }
}
