<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    /**
     * Autenticar usuário.
     *
     * Autentica as credenciais (e-mail e senha) e retorna o token de acesso Sanctum.
     *
     * @response status=200 scenario="Login efetuado com sucesso" {
     *   "token": "1|abc123def456...",
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
    public function __invoke(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return response()->json([
            'token' => $result['token'],
            'user' => new UserResource($result['user']),
        ], 200);
    }
}
