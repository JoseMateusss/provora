<?php

namespace App\Services\Ai\Contracts;

use App\Services\Ai\DTOs\GeneratedQuestionsResult;
use App\Services\Ai\DTOs\PromptPayload;

interface LlmProviderInterface
{
    /**
     * Envia o prompt e o texto extraído para o provedor de IA e retorna o resultado estruturado.
     *
     * @throws \App\Exceptions\GenerationFailedException
     */
    public function generateQuestions(PromptPayload $payload): GeneratedQuestionsResult;
}
