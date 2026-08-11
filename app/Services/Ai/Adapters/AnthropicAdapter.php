<?php

namespace App\Services\Ai\Adapters;

use App\Exceptions\GenerationFailedException;
use App\Services\Ai\Contracts\LlmProviderInterface;
use App\Services\Ai\DTOs\GeneratedQuestionsResult;
use App\Services\Ai\DTOs\PromptPayload;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicAdapter implements LlmProviderInterface
{
    public function __construct(
        protected string $apiKey,
        protected string $model = 'claude-3-5-sonnet-20241022'
    ) {}

    public function generateQuestions(PromptPayload $payload): GeneratedQuestionsResult
    {
        if (empty($this->apiKey)) {
            throw new GenerationFailedException('Anthropic API key não configurada.');
        }

        $systemPrompt = "Você é um especialista em elaboração de questões do ENEM. Gere {$payload->requestedCount} questões inéditas baseadas no texto fornecido, na área de {$payload->knowledgeArea}. Retorne estritamente um objeto JSON com a chave 'questions' contendo a lista de objetos com: 'statement', 'options' (A, B, C, D, E), 'correct_option' e 'explanation'.";

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
                ->timeout(120)
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $this->model,
                    'max_tokens' => 4096,
                    'system' => $systemPrompt,
                    'messages' => [
                        ['role' => 'user', 'content' => "Texto base:\n" . $payload->extractedText],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Anthropic API request failed', ['status' => $response->status(), 'body' => $response->body()]);
                throw new GenerationFailedException('Falha na comunicação com a Anthropic API.');
            }

            $data = $response->json();
            $rawText = $data['content'][0]['text'] ?? '{}';

            $content = json_decode($rawText, true);
            $questions = $content['questions'] ?? [];
            $tokensUsed = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0);

            return new GeneratedQuestionsResult(
                questions: $questions,
                tokensUsed: $tokensUsed,
                costEstimateUsd: ($tokensUsed / 1000) * 0.003,
                rawModelResponse: $response->body()
            );
        } catch (\Throwable $e) {
            if ($e instanceof GenerationFailedException) {
                throw $e;
            }

            Log::error('Erro ao gerar questões via AnthropicAdapter', ['exception' => $e->getMessage()]);
            throw new GenerationFailedException('Erro ao processar a geração de questões via Anthropic.', [], $e);
        }
    }
}
