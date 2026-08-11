<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\QuestionBatch
 */
class QuestionBatchResource extends JsonResource
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
            'document_id' => $this->document_id,
            'knowledge_area' => $this->knowledge_area,
            'difficulty' => $this->difficulty,
            'requested_count' => $this->requested_count,
            'generated_count' => $this->generated_count,
            'status' => $this->status,
            'error_message' => $this->when($this->status === 'failed' || ! empty($this->error_message), $this->error_message),
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
