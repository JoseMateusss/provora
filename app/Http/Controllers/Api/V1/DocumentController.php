<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Jobs\ExtractTextFromDocument;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Listar documentos do usuário.
     *
     * Retorna a lista paginada dos documentos enviados pelo professor autenticado.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Document::class);

        $documents = $request->user()
            ->documents()
            ->latest()
            ->paginate();

        return DocumentResource::collection($documents);
    }

    /**
     * Fazer upload de um arquivo PDF.
     *
     * Recebe um arquivo PDF (máx. 20MB), salva de forma privada e enfileira o job de extração de texto.
     *
     * @response status=202 scenario="PDF recebido e enfileirado para processamento" {
     *   "id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
     *   "original_filename": "apostila-quimica-cap3.pdf",
     *   "status": "pending"
     * }
     */
    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $file = $request->file('file');

        $path = $file->store('documents', 'local');

        $document = $request->user()->documents()->create([
            'original_filename' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'status' => 'pending',
        ]);

        ExtractTextFromDocument::dispatch($document);

        return response()->json([
            'id' => $document->id,
            'original_filename' => $document->original_filename,
            'status' => $document->status,
        ], 202);
    }

    /**
     * Visualizar detalhes e preview do documento.
     *
     * Retorna o status e a prévia do texto extraído (primeiros 500 caracteres).
     *
     * @response status=200 scenario="Documento extraído com sucesso" {
     *   "id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
     *   "status": "extracted",
     *   "original_filename": "apostila-quimica-cap3.pdf",
     *   "text_preview": "Texto extraído do documento PDF..."
     * }
     */
    public function show(Document $document): JsonResponse
    {
        Gate::authorize('view', $document);

        return response()->json(
            (new DocumentResource($document))->resolve()
        );
    }

    /**
     * Excluir um documento.
     *
     * Remove o documento do banco de dados e apaga o arquivo do armazenamento privado.
     *
     * @response status=200 scenario="Documento excluído com sucesso" {
     *   "message": "Documento e arquivo removidos com sucesso."
     * }
     */
    public function destroy(Document $document): JsonResponse
    {
        Gate::authorize('delete', $document);

        if (Storage::disk('local')->exists($document->storage_path)) {
            Storage::disk('local')->delete($document->storage_path);
        }

        $document->delete();

        return response()->json([
            'message' => 'Documento e arquivo removidos com sucesso.',
        ], 200);
    }
}
