# TASKS 03 — Geração de Questões

Referências obrigatórias: `PRD.md`, `specs/00-architecture.md`, `specs/03-question-generation.md`, `specs/06-billing-and-usage.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Implementação

- [x] Criar migration/model `QuestionBatch`.
- [x] Criar enums/regras de área, dificuldade e status.
- [x] Criar relacionamentos com User e Document.
- [x] Criar Form Request para geração.
- [x] Validar documento extraído e ownership.
- [x] Criar serviço de verificação do limite do plano.
- [x] Implementar `POST /api/v1/question-batches`.
- [x] Criar `GenerateQuestionsJob` na fila `generation`.
- [x] Versionar o prompt ENEM.
- [x] Criar cliente da Anthropic com `Illuminate\Http\Client`.
- [x] Implementar saída estruturada/tool use.
- [x] Validar schema de cada questão retornada.
- [x] Descartar itens inválidos individualmente.
- [x] Implementar estados `completed`, `partial` e `failed`.
- [x] Configurar retry/backoff.
- [x] Incrementar uso apenas após geração confirmada.
- [x] Implementar show/list/delete.
- [x] Criar Policy.
- [x] Aplicar rate limiting.
- [x] Criar testes usando fake/mock da Anthropic.
- [x] Documentar polling de 2–3 segundos no OpenAPI.

## Não implementar

Não implementar outras bancas ou WebSockets.
