# SPEC 06 — Planos, Billing e Controle de Uso

## Objetivo

Controlar o custo de geração através de limites por plano e permitir assinatura paga.

## Planos

MVP:

- `free`
- `pro`

Exemplo de regra inicial do produto:

```text
free = 10 questões/mês
```

O limite definitivo deve ser configurável e não espalhado pelo código.

## Controle de uso

Campo:

```text
users.questions_generated_this_month
```

Antes da geração:

```text
used + requested_count <= plan_limit
```

Caso contrário, retornar HTTP `403`.

## Erro

```json
{
  "error": {
    "code": "PLAN_LIMIT_EXCEEDED",
    "message": "Você atingiu o limite de questões do seu plano este mês.",
    "details": {
      "limit": 10,
      "used": 10
    }
  }
}
```

## Regra de contabilização

Incrementar o uso somente após geração confirmada.

Não consumir franquia quando a chamada de IA falhar completamente.

## Reset mensal

Job:

```text
ResetMonthlyUsage
```

Fila:

```text
billing
```

Gatilho:

```text
cron mensal
```

## Billing endpoints

### GET `/api/v1/billing/plans`

Retorna planos disponíveis.

### POST `/api/v1/billing/subscribe`

Inicia assinatura.

Pode retornar:

- client secret
- checkout URL

dependendo do gateway escolhido.

### POST `/api/v1/billing/webhook`

Sem autenticação Sanctum.

Obrigatório validar assinatura/autenticidade do gateway.

## Gateway

O documento original permite:

- Laravel Cashier + Stripe.
- Integração direta com Pagar.me.
- Integração direta com Asaas.

A escolha final do gateway não está definida no PRD/SPEC original e deve ser tomada antes da implementação do billing.

## Critérios de aceite

- Free não consegue exceder limite.
- Pro usa limite correspondente ao plano.
- Uso é refletido em `/auth/me`.
- Webhook não confia em requisição sem validação do provedor.
- Reset mensal é automatizado.
