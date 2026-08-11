<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $payload = [
            'name' => 'Maria Silva',
            'email' => 'maria@exemplo.com',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'plan',
                    'questions_generated_this_month',
                    'plan_limit',
                ],
            ])
            ->assertJson([
                'user' => [
                    'name' => 'Maria Silva',
                    'email' => 'maria@exemplo.com',
                    'plan' => 'free',
                    'questions_generated_this_month' => 0,
                    'plan_limit' => 10,
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'maria@exemplo.com',
            'name' => 'Maria Silva',
            'plan' => 'free',
        ]);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'maria@exemplo.com',
        ]);

        $payload = [
            'name' => 'Maria Silva Duplicada',
            'email' => 'maria@exemplo.com',
            'password' => 'senha123',
            'password_confirmation' => 'senha123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Os dados fornecidos são inválidos.',
                    'details' => [
                        'email' => ['Este e-mail já está em uso.'],
                    ],
                ],
            ]);
    }

    public function test_registration_fails_with_validation_errors(): void
    {
        $payload = [
            'name' => '',
            'email' => 'email-invalido',
            'password' => '123',
            'password_confirmation' => '456',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'error' => [
                    'code',
                    'message',
                    'details' => [
                        'name',
                        'email',
                        'password',
                    ],
                ],
            ]);
    }

    public function test_password_is_stored_hashed(): void
    {
        $payload = [
            'name' => 'João Souza',
            'email' => 'joao@exemplo.com',
            'password' => 'senhaSuperSegura123',
            'password_confirmation' => 'senhaSuperSegura123',
        ];

        $this->postJson('/api/v1/auth/register', $payload);

        $user = User::where('email', 'joao@exemplo.com')->firstOrFail();

        $this->assertNotEquals('senhaSuperSegura123', $user->password);
        $this->assertTrue(Hash::check('senhaSuperSegura123', $user->password));
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'maria@exemplo.com',
            'password' => 'senha123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'maria@exemplo.com',
            'password' => 'senha123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'plan',
                    'questions_generated_this_month',
                    'plan_limit',
                ],
            ])
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => 'maria@exemplo.com',
                ],
            ]);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'maria@exemplo.com',
            'password' => 'senha123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'maria@exemplo.com',
            'password' => 'senhaErrada',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Credenciais inválidas.',
                ],
            ]);
    }

    public function test_logout_revokes_current_token_only(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $token1 = $user->createToken('device_1')->plainTextToken;
        $token2 = $user->createToken('device_2')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token1)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Sessão encerrada com sucesso.',
            ]);

        $this->assertCount(1, $user->fresh()->tokens);
        $this->assertEquals('device_2', $user->fresh()->tokens->first()->name);
    }

    public function test_authenticated_user_can_get_me(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name' => 'Professor Carlos',
            'email' => 'carlos@exemplo.com',
            'plan' => 'pro',
            'questions_generated_this_month' => 15,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => 'Professor Carlos',
                    'email' => 'carlos@exemplo.com',
                    'plan' => 'pro',
                    'questions_generated_this_month' => 15,
                    'plan_limit' => 100,
                ],
            ]);
    }

    public function test_unauthenticated_user_cannot_access_me(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJson([
                'error' => [
                    'code' => 'UNAUTHORIZED',
                    'message' => 'Não autenticado.',
                ],
            ]);
    }
}
