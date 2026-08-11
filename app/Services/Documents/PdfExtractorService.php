<?php

namespace App\Services\Documents;

use App\Exceptions\DocumentExtractionFailedException;
use Smalot\PdfParser\Parser;
use Throwable;

class PdfExtractorService
{
    public function __construct(
        private ?Parser $parser = null
    ) {
        $this->parser = $parser ?? new Parser();
    }

    /**
     * Extrai o texto contido no arquivo PDF indicado.
     *
     * @throws DocumentExtractionFailedException
     */
    public function extract(string $filePath): string
    {
        if (! file_exists($filePath)) {
            throw new DocumentExtractionFailedException("Arquivo PDF não foi encontrado no caminho especificado: {$filePath}");
        }

        try {
            $pdf = $this->parser->parseFile($filePath);
            $rawText = $pdf->getText();
        } catch (Throwable $e) {
            throw new DocumentExtractionFailedException(
                message: "Falha ao extrair texto do documento PDF: {$e->getMessage()}",
                previous: $e
            );
        }

        return $this->sanitizeText($rawText);
    }

    /**
     * Sanitiza o texto extraído, removendo caracteres de controle indesejados.
     */
    public function sanitizeText(string $text): string
    {
        // Remove caracteres de controle ASCII/UTF-8 (preservando \n, \r, \t)
        $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        return trim($sanitized ?? '');
    }
}
