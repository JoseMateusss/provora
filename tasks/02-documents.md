# TASKS 02 — Documentos e Extração

Referências obrigatórias: `PRD.md`, `specs/00-architecture.md`, `specs/02-documents.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Implementação

- [x] Criar migration/model `Document` com UUID.
- [x] Criar relacionamento com `User`.
- [x] Configurar storage privado S3/R2.
- [x] Criar validação de upload PDF e limite de 20 MB.
- [x] Implementar `POST /api/v1/documents`.
- [x] Criar `ExtractTextFromDocument`.
- [x] Configurar fila `documents`.
- [x] Implementar transições `pending → processing → extracted|failed`.
- [x] Extrair texto com `smalot/pdfparser`.
- [x] Sanitizar texto extraído.
- [x] Persistir erro quando houver falha.
- [x] Implementar show/list/delete.
- [x] Criar Policy de ownership.
- [x] Remover arquivo do storage na exclusão.
- [x] Criar Resources e respostas padronizadas.
- [x] Criar Feature Tests e testes do Job.
- [x] Documentar endpoints no OpenAPI.

## Não implementar

Não gerar questões nesta etapa.
