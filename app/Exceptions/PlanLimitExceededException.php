<?php

namespace App\Exceptions;

class PlanLimitExceededException extends ApiException
{
    protected string $errorCode = 'PLAN_LIMIT_EXCEEDED';
    protected int $statusCode = 403;

    public function __construct(string $message = 'Você atingiu o limite de questões do seu plano este mês.', array $details = [], ?\Throwable $previous = null)
    {
        parent::__construct($message, $this->errorCode, $this->statusCode, $details, $previous);
    }
}
