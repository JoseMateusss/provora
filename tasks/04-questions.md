# TASKS 04 — Revisão e Edição de Questões

Referências obrigatórias: `PRD.md`, `specs/04-questions.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Implementação

- [ ] Criar migration/model `Question`.
- [ ] Usar `jsonb` para alternativas.
- [ ] Criar relacionamento com `QuestionBatch`.
- [ ] Persistir questões geradas com status `draft`.
- [ ] Criar Form Request para PATCH.
- [ ] Validar exatamente cinco alternativas A–E.
- [ ] Validar `correct_alternative`.
- [ ] Implementar `PATCH /api/v1/questions/{id}`.
- [ ] Alterar status para `edited` após edição.
- [ ] Implementar exclusão lógica via status `deleted`.
- [ ] Criar Policy baseada no ownership do batch.
- [ ] Criar Resource.
- [ ] Criar Feature Tests.
- [ ] Documentar endpoints no OpenAPI.
