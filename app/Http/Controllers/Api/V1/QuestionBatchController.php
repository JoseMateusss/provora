<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuestionBatchRequest;
use App\Http\Resources\QuestionBatchResource;
use App\Jobs\GenerateQuestionsJob;
use App\Models\QuestionBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class QuestionBatchController extends Controller
{
    /**
     * Listar lotes de questões do usuário.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', QuestionBatch::class);

        $batches = $request->user()
            ->questionBatches()
            ->latest()
            ->paginate();

        return QuestionBatchResource::collection($batches);
    }

    /**
     * Criar um novo lote de geração de questões.
     */
    public function store(StoreQuestionBatchRequest $request): JsonResponse
    {
        $batch = $request->user()->questionBatches()->create([
            'document_id' => $request->input('document_id'),
            'knowledge_area' => $request->input('knowledge_area'),
            'difficulty' => $request->input('difficulty'),
            'requested_count' => (int) $request->input('requested_count'),
            'status' => 'processing',
        ]);

        GenerateQuestionsJob::dispatch($batch);

        return response()->json([
            'id' => $batch->id,
            'status' => $batch->status,
            'requested_count' => $batch->requested_count,
        ], 202);
    }

    /**
     * Visualizar os detalhes de um lote de questões.
     */
    public function show(QuestionBatch $questionBatch): JsonResponse
    {
        Gate::authorize('view', $questionBatch);

        $questionBatch->load('questions');

        return response()->json(
            (new QuestionBatchResource($questionBatch))->resolve()
        );
    }

    /**
     * Excluir um lote de questões.
     */
    public function destroy(QuestionBatch $questionBatch): JsonResponse
    {
        Gate::authorize('delete', $questionBatch);

        $questionBatch->delete();

        return response()->json([
            'message' => 'Lote de questões removido com sucesso.',
        ], 200);
    }
}
