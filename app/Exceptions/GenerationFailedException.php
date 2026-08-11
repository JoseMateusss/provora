<?php

namespace App\Exceptions;

class GenerationFailedException extends ApiException
{
    protected string $errorCode = 'GENERATION_FAILED';
    protected int $statusCode = 500;

    public function __construct(string $message = 'Falha ao gerar questões via IA. Por favor, tente novamente.', array $details = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $this->errorCode, $this->statusCode, $details, $previous);
    }
}
