# SPEC 05 — Exportação de Lote para PDF

## Objetivo

Gerar um PDF pronto para impressão contendo as questões revisadas do lote.

## Modelo `exports`

| Campo | Tipo |
|---|---|
| id | uuid |
| question_batch_id | uuid FK |
| storage_path | string |
| status | enum(`processing`,`completed`,`failed`) |
| created_at | timestamp |

## Início

### POST `/api/v1/question-batches/{id}/export`

Resposta:

```json
{
  "export_id": "uuid",
  "status": "processing"
}
```

HTTP `202`.

## Job

Despachar `ExportBatchToPdf` na fila `exports`.

Fluxo:

1. Buscar lote.
2. Buscar questões não deletadas.
3. Renderizar template HTML.
4. Gerar PDF via `spatie/laravel-pdf` / Browsershot.
5. Armazenar no S3/R2.
6. Atualizar `storage_path`.
7. Marcar `completed`.

Em falha:

```text
status = failed
```

## Consulta

### GET `/api/v1/exports/{id}`

Quando concluído:

```json
{
  "export_id": "uuid",
  "status": "completed",
  "download_url": "https://signed-url-temporaria"
}
```

## Segurança

- Arquivo não deve ser público.
- `download_url` deve ser signed URL temporária.
- Validar ownership através do lote.

## Critérios de aceite

- Requisição de export não bloqueia.
- Questões `deleted` não aparecem.
- PDF final fica armazenado de forma privada.
- Usuário recebe URL temporária apenas quando concluído.
