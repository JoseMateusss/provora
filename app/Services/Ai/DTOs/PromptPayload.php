<?php

namespace App\Services\Ai\DTOs;

readonly class PromptPayload
{
    public function __construct(
        public string $extractedText,
        public int $requestedCount,
        public string $knowledgeArea,
        public ?string $difficulty = null,
        public ?string $additionalInstructions = null
    ) {}
}
