<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'question_batch_id',
        'user_id',
        'storage_path',
        'status',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(QuestionBatch::class, 'question_batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
