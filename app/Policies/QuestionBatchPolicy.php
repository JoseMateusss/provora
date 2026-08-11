<?php

namespace App\Policies;

use App\Models\QuestionBatch;
use App\Models\User;

class QuestionBatchPolicy
{
    /**
     * Determina se o usuário pode listar seus lotes.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determina se o usuário pode visualizar o lote especificado.
     */
    public function view(User $user, QuestionBatch $batch): bool
    {
        return $user->id === $batch->user_id;
    }

    /**
     * Determina se o usuário pode excluir o lote especificado.
     */
    public function delete(User $user, QuestionBatch $batch): bool
    {
        return $user->id === $batch->user_id;
    }
}
