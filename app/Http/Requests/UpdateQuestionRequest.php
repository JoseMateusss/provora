<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statement' => ['sometimes', 'required', 'string'],
            'alternatives' => ['sometimes', 'required', 'array', 'size:5'],
            'alternatives.*.letter' => ['required_with:alternatives', 'string', 'in:A,B,C,D,E'],
            'alternatives.*.text' => ['required_with:alternatives', 'string'],
            'correct_alternative' => ['sometimes', 'required', 'string', 'in:A,B,C,D,E'],
            'explanation' => ['sometimes', 'required', 'string'],
            'difficulty' => ['sometimes', 'required', 'string', 'in:facil,medio,dificil'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $alternatives = $this->input('alternatives');
            if (is_array($alternatives)) {
                $letters = array_column($alternatives, 'letter');
                sort($letters);
                if ($letters !== ['A', 'B', 'C', 'D', 'E']) {
                    $validator->errors()->add(
                        'alternatives',
                        'As alternativas devem conter exatamente as 5 letras A, B, C, D e E sem duplicidade.'
                    );
                }
            }
        });
    }
}
