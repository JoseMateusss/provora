<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'original_filename',
        'storage_path',
        'extracted_text',
        'error_message',
        'status',
    ];

    public function getTextPreviewAttribute(): ?string
    {
        if ($this->extracted_text === null) {
            return null;
        }

        return mb_substr($this->extracted_text, 0, 500);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questionBatches(): HasMany
    {
        return $this->hasMany(QuestionBatch::class);
    }
}
