<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\Documents\PdfExtractorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExtractTextFromDocument implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Document $document
    ) {}

    public function handle(PdfExtractorService $extractor): void
    {
        $this->document->update([
            'status' => 'processing',
        ]);

        try {
            $filePath = Storage::disk('local')->path($this->document->storage_path);

            $extractedText = $extractor->extract($filePath);

            $this->document->update([
                'extracted_text' => $extractedText,
                'status' => 'extracted',
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            $this->document->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
