<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Obter perfil do usuário autenticado.
     *
     * Retorna os dados do professor logado, incluindo plano ativo, consumo de questões no mês corrente e limite do plano.
     *
     * @response status=200 scenario="Autenticado" {
     *   "user": {
     *     "id": "9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d",
     *     "name": "Maria Silva",
     *     "email": "maria@exemplo.com",
     *     "plan": "free",
     *     "questions_generated_this_month": 0,
     *     "plan_limit": 10
     *   }
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ], 200);
    }
}
