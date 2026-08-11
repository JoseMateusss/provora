# SPEC 08 — Testes e Definition of Done do Backend MVP

## Objetivo

Definir quando o backend pode ser considerado pronto para integração com o frontend.

## Fluxos críticos que precisam de Feature Tests

### Fluxo 1 — Auth

- registro
- login
- logout
- me

### Fluxo 2 — Documento

- upload
- validação
- ownership
- processamento bem-sucedido
- falha de extração

### Fluxo 3 — Geração

- criação de batch
- limite de plano
- documento inválido/não processado
- processamento da IA
- batch completed
- batch partial
- batch failed

A API Anthropic deve ser fake/mock nos testes automatizados.

### Fluxo 4 — Questão

- edição
- validação de alternativas
- alteração de gabarito
- exclusão lógica
- ownership

### Fluxo 5 — Export

- criação
- geração
- arquivo persistido
- URL temporária
- ownership

### Fluxo 6 — Billing

- limite free
- atualização de assinatura
- webhook válido
- webhook inválido
- reset mensal

## Checklist de entrega

- [ ] Auth com Sanctum.
- [ ] Upload de documento.
- [ ] Extração em Job.
- [ ] Geração de lote em Job.
- [ ] Integração Anthropic.
- [ ] Validação do schema da IA.
- [ ] Persistência de questões.
- [ ] Edição e exclusão.
- [ ] Exportação PDF.
- [ ] Limites por plano.
- [ ] Billing básico.
- [ ] Webhook.
- [ ] OpenAPI.
- [ ] Rate limiting.
- [ ] Policies.
- [ ] Logs.
- [ ] Sentry.
- [ ] Feature tests dos fluxos críticos.

## Definition of Done por SPEC

Uma SPEC só pode ser considerada concluída quando:

1. Implementação está versionada.
2. Migrations necessárias existem.
3. Form Requests validam entrada.
4. Policies necessárias estão implementadas.
5. Resources padronizam saída.
6. Feature Tests passam.
7. Erros seguem contrato global.
8. Endpoints aparecem corretamente no OpenAPI.
9. Jobs possuem tratamento de falhas quando aplicável.
10. Não existem secrets hardcoded.
