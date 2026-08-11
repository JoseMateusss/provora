<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:pdf',
                'mimetypes:application/pdf',
                'max:20480',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'O arquivo PDF é obrigatório.',
            'file.file' => 'O envio deve ser um arquivo válido.',
            'file.mimes' => 'O arquivo enviado deve ser do tipo PDF.',
            'file.mimetypes' => 'O tipo MIME do arquivo deve ser application/pdf.',
            'file.max' => 'O tamanho máximo permitido para o arquivo é 20 MB.',
        ];
    }
}
