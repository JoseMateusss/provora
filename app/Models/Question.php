<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'question_batch_id',
        'user_id',
        'statement',
        'alternatives',
        'correct_alternative',
        'explanation',
        'difficulty',
        'status',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'alternatives' => 'array',
            'order' => 'integer',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(QuestionBatch::class, 'question_batch_id');
    }

    public function questionBatch(): BelongsTo
    {
        return $this->belongsTo(QuestionBatch::class, 'question_batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
