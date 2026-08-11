# SPEC 07 — Contratos de API, Segurança e Observabilidade

## Objetivo

Definir comportamentos transversais obrigatórios da API.

# 1. Padrão de erros

Todos os erros devem seguir:

```json
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Mensagem legível.",
    "details": {}
  }
}
```

## Códigos mínimos

- `VALIDATION_ERROR`
- `PLAN_LIMIT_EXCEEDED`
- `DOCUMENT_EXTRACTION_FAILED`
- `GENERATION_FAILED`
- `UNAUTHORIZED`
- `NOT_FOUND`
- `RATE_LIMITED`

# 2. OpenAPI

Utilizar OpenAPI/Swagger.

Sugestão original:

```text
dedoc/scramble
```

Documentação disponível em dev/staging:

```text
/api/documentation
```

Documentar:

- requests
- responses
- autenticação
- erros
- paginação
- polling
- enums
- códigos HTTP

# 3. Autorização

Todos os recursos precisam de ownership.

Usar Laravel Policies para:

- documents
- question_batches
- questions
- exports

Nunca confiar apenas em:

```php
->where('user_id', auth()->id())
```

como única barreira de autorização.

# 4. Segurança de arquivos

- MIME real.
- Máximo 20 MB.
- S3/R2 privado.
- Signed URLs.
- Sanitização do texto.
- Malware scan opcional no MVP.

# 5. Secrets

Guardar exclusivamente em ambiente/Forge:

- Anthropic API key.
- Storage credentials.
- Billing credentials.
- Sentry DSN.

Nunca versionar secrets.

# 6. CORS

Produção deve permitir explicitamente o domínio do frontend.

Não usar:

```text
*
```

em produção.

# 7. Observabilidade

## Sentry

Capturar exceptions não tratadas.

## Jobs

Cada job deve registrar:

- início
- fim
- duração
- identificadores relevantes
- sucesso/falha
- erro

Evitar registrar conteúdo sensível integral dos PDFs.

## Métrica operacional

Acompanhar custo médio de tokens por questão.
