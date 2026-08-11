<?php

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreQuestionBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_id' => [
                'required',
                'uuid',
                Rule::exists('documents', 'id')->where(function ($query) {
                    $query->where('user_id', $this->user()?->id);
                }),
            ],
            'knowledge_area' => [
                'required',
                'string',
                Rule::in(['linguagens', 'humanas', 'natureza', 'matematica']),
            ],
            'difficulty' => [
                'required',
                'string',
                Rule::in(['facil', 'medio', 'dificil']),
            ],
            'requested_count' => [
                'required',
                'integer',
                'min:1',
                'max:20',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document_id.required' => 'O ID do documento é obrigatório.',
            'document_id.uuid' => 'O ID do documento deve ser um UUID válido.',
            'document_id.exists' => 'O documento informado não existe ou não pertence a este usuário.',
            'knowledge_area.required' => 'A área do conhecimento é obrigatória.',
            'knowledge_area.in' => 'A área do conhecimento deve ser uma das seguintes: linguagens, humanas, natureza, matematica.',
            'difficulty.required' => 'A dificuldade é obrigatória.',
            'difficulty.in' => 'A dificuldade deve ser uma das seguintes: facil, medio, dificil.',
            'requested_count.required' => 'A quantidade solicitada de questões é obrigatória.',
            'requested_count.integer' => 'A quantidade solicitada deve ser um número inteiro.',
            'requested_count.min' => 'A quantidade de questões deve ser no mínimo 1.',
            'requested_count.max' => 'A quantidade de questões deve ser no máximo 20.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $user = $this->user();
                $documentId = $this->input('document_id');
                $requestedCount = (int) $this->input('requested_count');

                // 1. Validar status do documento
                $document = Document::where('id', $documentId)
                    ->where('user_id', $user->id)
                    ->first();

                if (! $document || $document->status !== 'extracted') {
                    $validator->errors()->add(
                        'document_id',
                        'O documento precisa estar com o texto extraído (status "extracted") para gerar questões.'
                    );
                }

                // 2. Validar limite do plano do usuário
                $remainingLimit = $user->planLimit() - $user->questions_generated_this_month;
                if ($requestedCount > $remainingLimit) {
                    $validator->errors()->add(
                        'requested_count',
                        "Você não possui limite suficiente em seu plano. Limite disponível: {$remainingLimit} questão(ões)."
                    );
                }
            },
        ];
    }
}
