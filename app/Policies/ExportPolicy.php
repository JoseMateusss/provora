<?php

namespace App\Policies;

use App\Models\Export;
use App\Models\QuestionBatch;
use App\Models\User;

class ExportPolicy
{
    /**
     * Determina se o usuário pode visualizar os detalhes da exportação.
     */
    public function view(User $user, Export $export): bool
    {
        return $user->id === $export->user_id || $user->id === $export->batch?->user_id;
    }

    /**
     * Determina se o usuário pode solicitar a exportação de um lote de questões.
     */
    public function create(User $user, QuestionBatch $batch): bool
    {
        return $user->id === $batch->user_id;
    }
}
