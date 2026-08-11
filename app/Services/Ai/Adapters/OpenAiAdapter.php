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

        $systemPrompt = config('prompts.enem_question_generator.system_prompt', "Você é um especialista em elaboração de questões do ENEM.");

        $userPrompt = strtr(
            config('prompts.enem_question_generator.user_prompt_template', "Área: :knowledge_area\nTexto: :extracted_text"),
            [
                ':knowledge_area' => $payload->knowledgeArea,
                ':difficulty' => $payload->difficulty ?? 'medio',
                ':requested_count' => (string) $payload->requestedCount,
                ':extracted_text' => $payload->extractedText,
            ]
        );

        $maxTokens = min(4096, max(1000, $payload->requestedCount * 600));

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(120)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => [
                            'name' => 'enem_questions_response',
                            'strict' => true,
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'questions' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'statement' => ['type' => 'string'],
                                                'alternatives' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        'type' => 'object',
                                                        'properties' => [
                                                            'letter' => ['type' => 'string'],
                                                            'text' => ['type' => 'string'],
                                                        ],
                                                        'required' => ['letter', 'text'],
                                                        'additionalProperties' => false,
                                                    ],
                                                ],
                                                'correct_alternative' => ['type' => 'string'],
                                                'explanation' => ['type' => 'string'],
                                                'difficulty' => ['type' => 'string'],
                                            ],
                                            'required' => [
                                                'statement',
                                                'alternatives',
                                                'correct_alternative',
                                                'explanation',
                                                'difficulty',
                                            ],
                                            'additionalProperties' => false,
                                        ],
                                    ],
                                ],
                                'required' => ['questions'],
                                'additionalProperties' => false,
                            ],
                        ],
                    ],
                    'temperature' => 1,
                    'max_completion_tokens' => $maxTokens,
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
