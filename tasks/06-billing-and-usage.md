# TASKS 06 — Billing e Controle de Uso

Referências obrigatórias: `PRD.md`, `specs/06-billing-and-usage.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Decisão pendente

Antes da implementação, definir o gateway do MVP entre as opções registradas na SPEC.

## Implementação

- [ ] Centralizar configuração dos planos e limites.
- [ ] Implementar serviço de consulta de limite/uso.
- [ ] Implementar `GET /api/v1/billing/plans`.
- [ ] Integrar gateway escolhido.
- [ ] Implementar `POST /api/v1/billing/subscribe`.
- [ ] Implementar webhook sem Sanctum.
- [ ] Validar assinatura/autenticidade do webhook.
- [ ] Sincronizar plano do usuário.
- [ ] Criar `ResetMonthlyUsage`.
- [ ] Agendar reset mensal.
- [ ] Padronizar `PLAN_LIMIT_EXCEEDED`.
- [ ] Expor uso e limite em `/auth/me`.
- [ ] Criar testes de limites, assinatura, webhook e reset.
- [ ] Documentar endpoints no OpenAPI.
