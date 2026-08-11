# SPEC 02 — Documentos e Extração de PDF

## Objetivo

Receber PDFs enviados pelo professor, armazená-los e extrair seu conteúdo textual de forma assíncrona.

## Modelo `documents`

| Campo | Tipo |
|---|---|
| id | uuid |
| user_id | uuid FK |
| original_filename | string |
| storage_path | string |
| status | enum(`pending`,`processing`,`extracted`,`failed`) |
| extracted_text | text nullable |
| error_message | text nullable |
| timestamps | timestamp |

Criar índice em `user_id`.

## Upload

### POST `/api/v1/documents`

`multipart/form-data`

Campo:

```text
file: PDF binário
```

Máximo: 20 MB.

Resposta:

```json
{
  "id": "uuid",
  "original_filename": "apostila-quimica-cap3.pdf",
  "status": "pending"
}
```

HTTP: `202 Accepted`.

## Processamento

Após persistir o arquivo:

1. Criar `Document` com status `pending`.
2. Despachar `ExtractTextFromDocument`.
3. Job altera status para `processing`.
4. Baixa/lê arquivo privado.
5. Extrai texto via `smalot/pdfparser`.
6. Sanitiza caracteres de controle.
7. Persiste `extracted_text`.
8. Marca `extracted`.
9. Em erro, marca `failed` e preenche `error_message`.

## Consulta

### GET `/api/v1/documents/{id}`

Quando concluído:

```json
{
  "id": "uuid",
  "status": "extracted",
  "original_filename": "apostila-quimica-cap3.pdf",
  "text_preview": "primeiros 500 caracteres..."
}
```

### GET `/api/v1/documents`

Lista paginada do usuário autenticado.

### DELETE `/api/v1/documents/{id}`

Remove o documento e seu arquivo do storage.

## Segurança

- Validar MIME real.
- Não confiar apenas na extensão.
- Limite de 20 MB.
- Arquivo privado.
- Ownership via Policy.
- ClamAV é opcional no MVP.

## Critérios de aceite

- PDF válido pode ser enviado.
- Requisição não espera extração terminar.
- Documento de outro usuário não pode ser acessado.
- Falha de extração fica registrada.
- Documento extraído possui preview.
