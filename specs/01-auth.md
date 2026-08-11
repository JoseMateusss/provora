# SPEC 01 — Autenticação e Usuários

## Objetivo

Permitir cadastro, autenticação e identificação do professor através da API.

## Dependências

- Laravel Sanctum.
- Tabela `users`.

## Modelo `users`

| Campo | Tipo | Regra |
|---|---|---|
| id | uuid | PK |
| name | string | obrigatório |
| email | string | unique |
| password | string | hash |
| plan | enum | `free`, `pro`; default `free` |
| questions_generated_this_month | integer | default 0 |
| stripe_id / gateway_customer_id | string nullable | billing |
| timestamps | timestamp | Laravel |

## Endpoints

### POST `/api/v1/auth/register`

Cria usuário e retorna token.

### POST `/api/v1/auth/login`

Autentica usuário e retorna token.

### POST `/api/v1/auth/logout`

Revoga o token atual.

### GET `/api/v1/auth/me`

Retorna:

- id
- name
- email
- plano
- uso mensal
- limite do plano

## Exemplo de registro

```json
{
  "name": "Maria Silva",
  "email": "maria@exemplo.com",
  "password": "senha123",
  "password_confirmation": "senha123"
}
```

Resposta `201`:

```json
{
  "token": "1|abc123...",
  "user": {
    "id": "uuid",
    "name": "Maria Silva",
    "email": "maria@exemplo.com",
    "plan": "free"
  }
}
```

## Critérios de aceite

- Usuário consegue registrar-se.
- E-mail duplicado é rejeitado.
- Senha é armazenada com hash.
- Login inválido não gera token.
- Logout revoga apenas o token atual.
- `/auth/me` exige autenticação.
