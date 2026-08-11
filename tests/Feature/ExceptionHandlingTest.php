<?php

namespace Tests\Feature;

use App\Exceptions\PlanLimitExceededException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_not_found_returns_standardized_json_error(): void
    {
        $response = $this->getJson('/api/v1/non-existing-route');

        $response->assertStatus(404)
            ->assertJson([
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Recurso não encontrado.',
                ],
            ]);
    }

    public function test_custom_api_exception_returns_standardized_json_error(): void
    {
        Route::get('/api/v1/test-plan-limit', function () {
            throw new PlanLimitExceededException();
        });

        $response = $this->getJson('/api/v1/test-plan-limit');

        $response->assertStatus(403)
            ->assertJson([
                'error' => [
                    'code' => 'PLAN_LIMIT_EXCEEDED',
                    'message' => 'Você atingiu o limite de questões do seu plano este mês.',
                ],
            ]);
    }
}
