<?php

namespace App\Jobs;

use App\Models\Export;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelPdf\Facades\Pdf;

class ExportBatchToPdf implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [5, 15];

    public function __construct(
        public Export $export
    ) {
        $this->onQueue('exports');
    }

    public function handle(): void
    {
        $export = $this->export->fresh(['batch']);

        if (! $export || $export->status !== 'processing') {
            return;
        }

        $batch = $export->batch;

        if (! $batch) {
            $export->update(['status' => 'failed']);
            return;
        }

        try {
            $questions = $batch->activeQuestions()
                ->orderBy('order', 'asc')
                ->get();

            $storagePath = 'exports/' . $export->id . '.pdf';
            $disk = config('filesystems.default', 'local');

            Pdf::view('pdf.questions_export', [
                'batch' => $batch,
                'questions' => $questions,
            ])
            ->disk($disk)
            ->save($storagePath);

            $export->update([
                'storage_path' => $storagePath,
                'status' => 'completed',
            ]);

            Log::info('Exportação de PDF concluída com sucesso', [
                'export_id' => $export->id,
                'batch_id' => $batch->id,
                'questions_count' => $questions->count(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao gerar PDF de exportação', [
                'export_id' => $export->id,
                'batch_id' => $batch->id ?? null,
                'exception' => $e->getMessage(),
            ]);

            $export->update(['status' => 'failed']);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Export::where('id', $this->export->id)->update([
            'status' => 'failed',
        ]);
    }
}
