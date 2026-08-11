# TASKS 05 — Exportação PDF

Referências obrigatórias: `PRD.md`, `specs/05-export.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Implementação

- [ ] Criar migration/model `Export`.
- [ ] Criar relacionamento com `QuestionBatch`.
- [ ] Implementar `POST /api/v1/question-batches/{id}/export`.
- [ ] Criar `ExportBatchToPdf` na fila `exports`.
- [ ] Criar template de impressão.
- [ ] Ignorar questões com status `deleted`.
- [ ] Gerar PDF com `spatie/laravel-pdf` / Browsershot.
- [ ] Armazenar PDF privadamente no S3/R2.
- [ ] Implementar estados `processing`, `completed` e `failed`.
- [ ] Implementar `GET /api/v1/exports/{id}`.
- [ ] Gerar signed URL temporária.
- [ ] Criar Policy.
- [ ] Criar testes.
- [ ] Documentar endpoints no OpenAPI.
