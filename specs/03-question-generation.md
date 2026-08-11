# SPEC 03 — Geração de Lotes e Integração com IA

## Objetivo

Gerar lotes de questões no estilo ENEM a partir do texto extraído de um documento.

## Modelo `question_batches`

| Campo | Tipo |
|---|---|
| id | uuid |
| user_id | uuid FK |
| document_id | uuid FK |
| knowledge_area | enum(`linguagens`,`humanas`,`natureza`,`matematica`) |
| difficulty | enum(`facil`,`medio`,`dificil`) |
| requested_count | integer |
| status | enum(`processing`,`completed`,`failed`,`partial`) |
| error_message | text nullable |
| timestamps | timestamp |

Índices:

- `user_id`
- `(user_id, created_at)`

## Endpoint de criação

### POST `/api/v1/question-batches`

```json
{
  "document_id": "uuid",
  "knowledge_area": "natureza",
  "difficulty": "medio",
  "requested_count": 10
}
```

Regras:

- `requested_count`: 1–20.
- Documento deve pertencer ao usuário.
- Documento precisa estar `extracted`.
- Usuário precisa possuir limite disponível.

Resposta:

```json
{
  "id": "uuid",
  "status": "processing",
  "requested_count": 10
}
```

HTTP `202`.

## Job

Despachar `GenerateQuestionsJob` na fila `generation`.

O Job deve:

1. Recuperar texto extraído.
2. Montar prompt versionado.
3. Chamar API OpenAI.
4. Receber saída estruturada.
5. Validar cada questão.
6. Persistir itens válidos.
7. Atualizar batch.
8. Incrementar uso somente depois do sucesso confirmado.

## Prompt

Manter prompt fixo e versionado, por exemplo:

```text
config/prompts/enem_question_generator.php
```

Diretrizes:

1. Basear-se exclusivamente no texto fornecido.
2. Seguir estilo ENEM.
3. Produzir 5 alternativas plausíveis.
4. Ter exatamente uma correta.
5. Incluir gabarito comentado.
6. Respeitar dificuldade solicitada.
7. Retornar saída estruturada.

## Schema esperado

```json
{
  "questions": [
    {
      "statement": "string",
      "alternatives": [
        {"letter": "A", "text": "string"},
        {"letter": "B", "text": "string"},
        {"letter": "C", "text": "string"},
        {"letter": "D", "text": "string"},
        {"letter": "E", "text": "string"}
      ],
      "correct_alternative": "A",
      "explanation": "string",
      "difficulty": "medio"
    }
  ]
}
```

## Integração Anthropic

- Cliente: `Illuminate\Http\Client`.
- `temperature`: 0.3.
- `max_tokens`: dimensionado pela quantidade.
- Estimativa inicial: 400–600 tokens por questão + buffer.
- Preferir tool use/function calling em vez de JSON livre.

## Validação pós-resposta

Cada questão precisa:

- Ter `statement`.
- Ter exatamente cinco alternativas.
- Usar letras A–E.
- Possuir uma única alternativa correta.
- Ter `correct_alternative` existente.
- Possuir `explanation`.
- Possuir dificuldade válida.

Itens inválidos são descartados individualmente.

Se o total válido for menor que o solicitado:

```text
status = partial
```

## Retry

Falhas de timeout/5xx:

- Máximo de duas novas tentativas.
- Backoff exponencial.
- Utilizar mecanismos de retry do Laravel Queue.

## Consulta

### GET `/api/v1/question-batches/{id}`

O cliente deve fazer polling a cada 2–3 segundos enquanto:

```text
status = processing
```

### GET `/api/v1/question-batches`

Lista paginada.

### DELETE `/api/v1/question-batches/{id}`

Remove o lote.

## Rate limit

Aplicar throttle à criação de lotes.

Referência inicial:

```text
10 requisições/minuto
```

## Critérios de aceite

- Geração não bloqueia HTTP.
- Batch possui acompanhamento por status.
- IA recebe apenas conteúdo do documento correspondente.
- Respostas inválidas não derrubam necessariamente o lote inteiro.
- Uso do plano não é cobrado quando a geração falha totalmente.
