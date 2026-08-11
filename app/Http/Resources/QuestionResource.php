<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Question
 */
class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'statement' => $this->statement,
            'options' => $this->options,
            'correct_option' => $this->correct_option,
            'explanation' => $this->explanation,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
