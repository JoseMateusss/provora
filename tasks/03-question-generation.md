# TASKS 03 — Geração de Questões

Referências obrigatórias: `PRD.md`, `specs/00-architecture.md`, `specs/03-question-generation.md`, `specs/06-billing-and-usage.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Implementação

- [ ] Criar migration/model `QuestionBatch`.
- [ ] Criar enums/regras de área, dificuldade e status.
- [ ] Criar relacionamentos com User e Document.
- [ ] Criar Form Request para geração.
- [ ] Validar documento extraído e ownership.
- [ ] Criar serviço de verificação do limite do plano.
- [ ] Implementar `POST /api/v1/question-batches`.
- [ ] Criar `GenerateQuestionsJob` na fila `generation`.
- [ ] Versionar o prompt ENEM.
- [ ] Criar cliente da Anthropic com `Illuminate\Http\Client`.
- [ ] Implementar saída estruturada/tool use.
- [ ] Validar schema de cada questão retornada.
- [ ] Descartar itens inválidos individualmente.
- [ ] Implementar estados `completed`, `partial` e `failed`.
- [ ] Configurar retry/backoff.
- [ ] Incrementar uso apenas após geração confirmada.
- [ ] Implementar show/list/delete.
- [ ] Criar Policy.
- [ ] Aplicar rate limiting.
- [ ] Criar testes usando fake/mock da Anthropic.
- [ ] Documentar polling de 2–3 segundos no OpenAPI.

## Não implementar

Não implementar outras bancas ou WebSockets.
