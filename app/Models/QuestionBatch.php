<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBatch extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'document_id',
        'knowledge_area',
        'difficulty',
        'requested_count',
        'generated_count',
        'status',
        'error_message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'question_batch_id');
    }

    public function activeQuestions(): HasMany
    {
        return $this->hasMany(Question::class, 'question_batch_id')
            ->where('status', '!=', 'deleted');
    }

    public function exports(): HasMany
    {
        return $this->hasMany(Export::class);
    }
}
