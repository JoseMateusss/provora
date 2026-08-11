# TASKS 05 — Exportação PDF

Referências obrigatórias: `PRD.md`, `specs/05-export.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Implementação

- [x] Criar migration/model `Export`.
- [x] Criar relacionamento com `QuestionBatch`.
- [x] Implementar `POST /api/v1/question-batches/{id}/export`.
- [x] Criar `ExportBatchToPdf` na fila `exports`.
- [x] Criar template de impressão.
- [x] Ignorar questões com status `deleted`.
- [x] Gerar PDF com `spatie/laravel-pdf` / Browsershot.
- [x] Armazenar PDF privadamente no S3/R2.
- [x] Implementar estados `processing`, `completed` e `failed`.
- [x] Implementar `GET /api/v1/exports/{id}`.
- [x] Gerar signed URL temporária.
- [x] Criar Policy.
- [x] Criar testes.
- [x] Documentar endpoints no OpenAPI.
