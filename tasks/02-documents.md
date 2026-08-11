# TASKS 02 — Documentos e Extração

Referências obrigatórias: `PRD.md`, `specs/00-architecture.md`, `specs/02-documents.md`, `specs/07-api-errors-security-observability.md` e `specs/08-testing-and-definition-of-done.md`.

## Implementação

- [ ] Criar migration/model `Document` com UUID.
- [ ] Criar relacionamento com `User`.
- [ ] Configurar storage privado S3/R2.
- [ ] Criar validação de upload PDF e limite de 20 MB.
- [ ] Implementar `POST /api/v1/documents`.
- [ ] Criar `ExtractTextFromDocument`.
- [ ] Configurar fila `documents`.
- [ ] Implementar transições `pending → processing → extracted|failed`.
- [ ] Extrair texto com `smalot/pdfparser`.
- [ ] Sanitizar texto extraído.
- [ ] Persistir erro quando houver falha.
- [ ] Implementar show/list/delete.
- [ ] Criar Policy de ownership.
- [ ] Remover arquivo do storage na exclusão.
- [ ] Criar Resources e respostas padronizadas.
- [ ] Criar Feature Tests e testes do Job.
- [ ] Documentar endpoints no OpenAPI.

## Não implementar

Não gerar questões nesta etapa.
