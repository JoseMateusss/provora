<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

abstract class ApiException extends Exception
{
    protected string $errorCode = 'INTERNAL_ERROR';
    protected int $statusCode = 500;
    protected array $details = [];

    public function __construct(string $message = '', string $errorCode = '', int $statusCode = 0, array $details = [], ?\Throwable $previous = null)
    {
        if ($errorCode !== '') {
            $this->errorCode = $errorCode;
        }

        if ($statusCode !== 0) {
            $this->statusCode = $statusCode;
        }

        $this->details = $details;

        parent::__construct($message, $this->statusCode, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->getMessage(),
                'details' => (object) $this->details,
            ],
        ], $this->statusCode);
    }
}
