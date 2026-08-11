# TASKS 01 — Autenticação e Usuários

Referências obrigatórias: `PRD.md`, `specs/00-architecture.md`, `specs/01-auth.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Implementação

- [ ] Configurar Laravel Sanctum para autenticação via Bearer Token.
- [ ] Ajustar `users` para UUID.
- [ ] Criar campos `plan`, `questions_generated_this_month` e identificador nullable do gateway.
- [ ] Criar Form Requests de registro e login.
- [ ] Implementar registro.
- [ ] Implementar login.
- [ ] Implementar logout revogando somente o token atual.
- [ ] Implementar `/api/v1/auth/me`.
- [ ] Criar Resource para respostas de usuário.
- [ ] Padronizar erros de autenticação e validação.
- [ ] Criar Feature Tests.
- [ ] Garantir documentação OpenAPI dos endpoints.

## Não implementar

Não avançar para upload, geração, billing ou demais funcionalidades.
