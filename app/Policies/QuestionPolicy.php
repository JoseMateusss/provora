<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    /**
     * Determina se o usuário pode listar ou ver a questão.
     */
    public function view(User $user, Question $question): bool
    {
        return $user->id === $question->batch?->user_id || $user->id === $question->user_id;
    }

    /**
     * Determina se o usuário pode atualizar a questão.
     */
    public function update(User $user, Question $question): bool
    {
        return $user->id === $question->batch?->user_id || $user->id === $question->user_id;
    }

    /**
     * Determina se o usuário pode excluir a questão.
     */
    public function delete(User $user, Question $question): bool
    {
        return $user->id === $question->batch?->user_id || $user->id === $question->user_id;
    }
}
