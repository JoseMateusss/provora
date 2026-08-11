# TASKS 01 — Autenticação e Usuários

Referências obrigatórias: `PRD.md`, `specs/00-architecture.md`, `specs/01-auth.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Implementação

- [x] Configurar Laravel Sanctum para autenticação via Bearer Token.
- [x] Ajustar `users` para UUID.
- [x] Criar campos `plan`, `questions_generated_this_month` e identificador nullable do gateway.
- [x] Criar Form Requests de registro e login.
- [x] Implementar registro.
- [x] Implementar login.
- [x] Implementar logout revogando somente o token atual.
- [x] Implementar `/api/v1/auth/me`.
- [x] Criar Resource para respostas de usuário.
- [x] Padronizar erros de autenticação e validação.
- [x] Criar Feature Tests.
- [x] Garantir documentação OpenAPI dos endpoints.

## Não implementar

Não avançar para upload, geração, billing ou demais funcionalidades.
