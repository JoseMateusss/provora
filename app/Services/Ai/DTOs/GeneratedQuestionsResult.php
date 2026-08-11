<?php

namespace App\Services\Ai\DTOs;

readonly class GeneratedQuestionsResult
{
    /**
     * @param array<int, array{
     *     statement: string,
     *     options: array<string, string>,
     *     correct_option: string,
     *     explanation: string
     * }> $questions
     */
    public function __construct(
        public array $questions,
        public int $tokensUsed = 0,
        public float $costEstimateUsd = 0.0,
        public ?string $rawModelResponse = null
    ) {}
}
