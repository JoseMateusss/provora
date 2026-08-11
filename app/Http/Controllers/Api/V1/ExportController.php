<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExportResource;
use App\Jobs\ExportBatchToPdf;
use App\Models\Export;
use App\Models\QuestionBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ExportController extends Controller
{
    /**
     * Iniciar a exportação de um lote de questões para PDF.
     */
    public function store(Request $request, QuestionBatch $questionBatch): JsonResponse
    {
        Gate::authorize('create', [Export::class, $questionBatch]);

        $export = Export::create([
            'question_batch_id' => $questionBatch->id,
            'user_id' => $request->user()->id,
            'status' => 'processing',
        ]);

        ExportBatchToPdf::dispatch($export);

        return response()->json([
            'export_id' => $export->id,
            'status' => $export->status,
        ], 202);
    }

    /**
     * Consultar o status e obter a URL de download da exportação.
     */
    public function show(Export $export): JsonResponse
    {
        Gate::authorize('view', $export);

        return response()->json(
            (new ExportResource($export))->resolve()
        );
    }
}
