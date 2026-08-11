<?php

use App\Exceptions\ApiException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/documentation*')) {
                return null;
            }

            if (! $request->is('api/*') && ! $request->wantsJson()) {
                return null;
            }

            if ($e instanceof ApiException) {
                return $e->render($request);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'Os dados fornecidos são inválidos.',
                        'details' => $e->errors(),
                    ],
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                $message = ($e->getMessage() && $e->getMessage() !== 'Unauthenticated.') 
                    ? $e->getMessage() 
                    : 'Não autenticado.';

                return response()->json([
                    'error' => [
                        'code' => 'UNAUTHORIZED',
                        'message' => $message,
                        'details' => (object) [],
                    ],
                ], 401);
            }

            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                return response()->json([
                    'error' => [
                        'code' => 'FORBIDDEN',
                        'message' => 'Você não tem permissão para acessar este recurso.',
                        'details' => (object) [],
                    ],
                ], 403);
            }

            if ($e instanceof NotFoundHttpException) {
                return response()->json([
                    'error' => [
                        'code' => 'NOT_FOUND',
                        'message' => 'Recurso não encontrado.',
                        'details' => (object) [],
                    ],
                ], 404);
            }

            if ($e instanceof ThrottleRequestsException) {
                return response()->json([
                    'error' => [
                        'code' => 'RATE_LIMITED',
                        'message' => 'Muitas requisições. Aguarde um momento antes de tentar novamente.',
                        'details' => (object) [],
                    ],
                ], 429);
            }

            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
            $message = config('app.debug') ? $e->getMessage() : 'Ocorreu um erro interno no servidor.';

            return response()->json([
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => $message,
                    'details' => config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ] : (object) [],
                ],
            ], $statusCode >= 400 && $statusCode < 600 ? $statusCode : 500);
        });
    })->create();
