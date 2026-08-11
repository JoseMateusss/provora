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
        'batch_id',
        'user_id',
        'statement',
        'options',
        'correct_option',
        'explanation',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(QuestionBatch::class, 'batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
