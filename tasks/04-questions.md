# TASKS 04 — Revisão e Edição de Questões

Referências obrigatórias: `PRD.md`, `specs/04-questions.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Implementação

- [x] Criar migration/model `Question`.
- [x] Usar `jsonb` para alternativas.
- [x] Criar relacionamento com `QuestionBatch`.
- [x] Persistir questões geradas com status `draft`.
- [x] Criar Form Request para PATCH.
- [x] Validar exatamente cinco alternativas A–E.
- [x] Validar `correct_alternative`.
- [x] Implementar `PATCH /api/v1/questions/{id}`.
- [x] Alterar status para `edited` após edição.
- [x] Implementar exclusão lógica via status `deleted`.
- [x] Criar Policy baseada no ownership do batch.
- [x] Criar Resource.
- [x] Criar Feature Tests.
- [x] Documentar endpoints no OpenAPI.
