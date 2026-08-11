<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Encerrar sessão.
     *
     * Revoga o token de acesso Sanctum atual utilizado na requisição.
     *
     * @response status=200 scenario="Logout efetuado com sucesso" {
     *   "message": "Sessão encerrada com sucesso."
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $request->user()?->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Sessão encerrada com sucesso.',
        ], 200);
    }
}
