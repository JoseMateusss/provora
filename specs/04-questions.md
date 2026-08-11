# SPEC 04 — Questões, Revisão e Edição

## Objetivo

Persistir e permitir a revisão manual das questões geradas.

## Modelo `questions`

| Campo | Tipo |
|---|---|
| id | uuid |
| question_batch_id | uuid FK |
| statement | text |
| alternatives | jsonb |
| correct_alternative | char(1) |
| explanation | text |
| difficulty | enum(`facil`,`medio`,`dificil`) |
| status | enum(`draft`,`edited`,`approved`,`deleted`) |
| order | integer |
| timestamps | timestamp |

Criar índice em `question_batch_id`.

## Alternatives

Formato:

```json
[
  {"letter": "A", "text": "..."},
  {"letter": "B", "text": "..."},
  {"letter": "C", "text": "..."},
  {"letter": "D", "text": "..."},
  {"letter": "E", "text": "..."}
]
```

## Edição

### PATCH `/api/v1/questions/{id}`

Campos parciais aceitos:

```json
{
  "statement": "Texto revisado...",
  "alternatives": [
    {"letter": "A", "text": "..."},
    {"letter": "B", "text": "..."},
    {"letter": "C", "text": "..."},
    {"letter": "D", "text": "..."},
    {"letter": "E", "text": "..."}
  ],
  "correct_alternative": "B",
  "explanation": "Explicação revisada..."
}
```

Após edição:

```text
status = edited
```

## Exclusão

### DELETE `/api/v1/questions/{id}`

Não precisa excluir fisicamente.

Marcar:

```text
status = deleted
```

## Ownership

O usuário só pode editar/excluir uma questão quando o batch relacionado pertence a ele.

Implementar com Policy.

## Critérios de aceite

- Edição parcial é aceita.
- Sempre existem cinco alternativas válidas.
- Gabarito só aceita A–E.
- Questão editada muda de status.
- Questão excluída logicamente deixa de participar da exportação.
