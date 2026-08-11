<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class QuestionController extends Controller
{
    /**
     * Visualizar os detalhes de uma questão.
     */
    public function show(Question $question): JsonResponse
    {
        Gate::authorize('view', $question);

        return response()->json(
            (new QuestionResource($question))->resolve()
        );
    }

    /**
     * Editar parcialmente uma questão.
     */
    public function update(UpdateQuestionRequest $request, Question $question): JsonResponse
    {
        Gate::authorize('update', $question);

        $data = $request->validated();
        $data['status'] = 'edited';

        $question->update($data);

        return response()->json(
            (new QuestionResource($question->fresh()))->resolve()
        );
    }

    /**
     * Excluir logicamente uma questão (status = deleted).
     */
    public function destroy(Question $question): JsonResponse
    {
        Gate::authorize('delete', $question);

        $question->update(['status' => 'deleted']);

        return response()->json([
            'message' => 'Questão excluída com sucesso.',
            'id' => $question->id,
            'status' => 'deleted',
        ], 200);
    }
}
