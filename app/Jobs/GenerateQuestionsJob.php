<?php

namespace App\Jobs;

use App\Models\QuestionBatch;
use App\Services\Ai\Contracts\LlmProviderInterface;
use App\Services\Ai\DTOs\PromptPayload;
use App\Services\Ai\QuestionValidatorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateQuestionsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [5, 15];

    public function __construct(
        public QuestionBatch $batch
    ) {
        $this->onQueue('generation');
    }

    public function handle(
        LlmProviderInterface $llmProvider,
        QuestionValidatorService $validator
    ): void {
        $batch = $this->batch->fresh(['document', 'user']);

        if (! $batch || $batch->status !== 'processing') {
            return;
        }

        $document = $batch->document;

        if (! $document || empty($document->extracted_text)) {
            $batch->update([
                'status' => 'failed',
                'error_message' => 'Documento sem texto extraído disponível para geração.',
            ]);

            return;
        }

        try {
            $payload = new PromptPayload(
                extractedText: $document->extracted_text,
                requestedCount: $batch->requested_count,
                knowledgeArea: $batch->knowledge_area,
                difficulty: $batch->difficulty
            );

            $result = $llmProvider->generateQuestions($payload);

            $validQuestions = $validator->validateAndFilter(
                rawQuestions: $result->questions,
                fallbackDifficulty: $batch->difficulty
            );

            $validCount = count($validQuestions);

            DB::transaction(function () use ($batch, $validQuestions, $validCount) {
                foreach ($validQuestions as $q) {
                    $batch->questions()->create([
                        'user_id' => $batch->user_id,
                        'statement' => $q['statement'],
                        'options' => $q['options'],
                        'correct_option' => $q['correct_option'],
                        'explanation' => $q['explanation'],
                        'status' => 'generated',
                    ]);
                }

                if ($validCount === 0) {
                    $batch->status = 'failed';
                    $batch->error_message = 'Nenhuma questão válida foi gerada a partir da resposta da IA.';
                } elseif ($validCount < $batch->requested_count) {
                    $batch->status = 'partial';
                } else {
                    $batch->status = 'completed';
                }

                $batch->generated_count = $validCount;
                $batch->save();

                if ($validCount > 0) {
                    $batch->user->increment('questions_generated_this_month', $validCount);
                }
            });
        } catch (\Throwable $e) {
            Log::error('Erro no GenerateQuestionsJob', [
                'batch_id' => $batch->id,
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        QuestionBatch::where('id', $this->batch->id)->update([
            'status' => 'failed',
            'error_message' => 'Falha no processamento de geração: ' . $exception->getMessage(),
        ]);
    }
}
