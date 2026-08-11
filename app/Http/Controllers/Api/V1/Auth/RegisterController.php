<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /**
     * Registrar novo usuário.
     *
     * Cria uma nova conta de professor com o plano gratuito (free) e retorna o token de acesso Sanctum.
     *
     * @response status=201 scenario="Usuário registrado com sucesso" {
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
    public function __invoke(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $user = $action->execute($request->validated());

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ], 201);
    }
}
