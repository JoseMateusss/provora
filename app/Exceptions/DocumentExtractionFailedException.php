<?php

namespace App\Exceptions;

class DocumentExtractionFailedException extends ApiException
{
    protected string $errorCode = 'DOCUMENT_EXTRACTION_FAILED';
    protected int $statusCode = 422;

    public function __construct(string $message = 'Falha ao extrair texto do documento PDF.', array $details = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $this->errorCode, $this->statusCode, $details, $previous);
    }
}
