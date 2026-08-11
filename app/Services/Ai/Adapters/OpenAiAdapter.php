<?php

namespace App\Services\Ai\Adapters;

use App\Exceptions\GenerationFailedException;
use App\Services\Ai\Contracts\LlmProviderInterface;
use App\Services\Ai\DTOs\GeneratedQuestionsResult;
use App\Services\Ai\DTOs\PromptPayload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiAdapter implements LlmProviderInterface
{
    public function __construct(
        protected string $apiKey,
        protected string $model = 'gpt-4o-mini'
    ) {}

    public function generateQuestions(PromptPayload $payload): GeneratedQuestionsResult
    {
        if (empty($this->apiKey)) {
            throw new GenerationFailedException('OpenAI API key não configurada.');
        }

        $systemPrompt = "Você é um especialista em elaboração de questões do ENEM. Gere {$payload->requestedCount} questões inéditas baseadas no texto fornecido, na área de {$payload->knowledgeArea}. Retorne estritamente um JSON com a chave 'questions' contendo uma lista de objetos com: 'statement' (enunciado), 'options' (mapa A, B, C, D, E), 'correct_option' (letra) e 'explanation' (gabarito comentado).";

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(120)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => "Texto base:\n" . $payload->extractedText],
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'temperature' => 0.7,
                ]);

            if ($response->failed()) {
                Log::error('OpenAI API request failed', ['status' => $response->status(), 'body' => $response->body()]);
                throw new GenerationFailedException('Falha na comunicação com a OpenAI API.');
            }

            $data = $response->json();
            $content = json_decode($data['choices'][0]['message']['content'] ?? '{}', true);

            $questions = $content['questions'] ?? [];
            $tokensUsed = $data['usage']['total_tokens'] ?? 0;

            return new GeneratedQuestionsResult(
                questions: $questions,
                tokensUsed: $tokensUsed,
                costEstimateUsd: ($tokensUsed / 1000) * 0.00015,
                rawModelResponse: $response->body()
            );
        } catch (\Throwable $e) {
            if ($e instanceof GenerationFailedException) {
                throw $e;
            }

            Log::error('Erro ao gerar questões via OpenAiAdapter', ['exception' => $e->getMessage()]);
            throw new GenerationFailedException('Erro ao processar a geração de questões via OpenAI.', [], $e);
        }
    }
}
