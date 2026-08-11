# SPEC 00 — Arquitetura Backend

## 1. Objetivo e Escopo do MVP

Definir a arquitetura técnica base do backend da plataforma **Provora**.

No MVP, o backend atende exclusivamente a aplicação web responsiva para professores do ENEM, fornecendo APIs RESTful para:
- Autenticação e gestão de conta do professor.
- Upload e extração de texto de documentos PDF pesquisáveis.
- Geração de lotes de questões no estilo ENEM via IA (com alternativas, resposta e gabarito comentado).
- Edição, revisão e exclusão lógica de questões.
- Exportação de lotes revisados para PDF formatado para impressão.
- Gestão de planos, limites de uso mensal e cobrança por assinatura.

### Fora do escopo arquitetural do MVP
- WebSockets / tempo real (o MVP utiliza HTTP Polling).
- Aplicação mobile nativa (a API v1 é mantida limpa e stateless para permitir consumo mobile futuro).
- Banco de questões público/compartilhado.

---

## 2. Stack Tecnológica

| Camada | Tecnologia | Detalhes / Justificativa |
|---|---|---|
| Framework | **Laravel 11.x** | MVC moderno, suporte a Queue/Horizon, Cashier e Sanctum. |
| Linguagem | **PHP 8.3+** | Performance aprimorada e suporte a tipos estritos. |
| Banco de Dados | **PostgreSQL 16** | Suporte robusto a JSONB (armazenamento de alternativas) e UUIDs nativos. |
| Cache & Filas | **Redis 7.x + Laravel Horizon** | Gerenciamento de filas prioritárias, throttling e controle de concorrência. |
| Storage de Arquivos | **S3-compatible (Cloudflare R2)** | Armazenamento privado e de baixo custo com download via Signed URLs. |
| Extração de PDF | `smalot/pdfparser` | Parser leve em PHP para extração de texto de PDFs pesquisáveis. |
| Gerador de PDF de Saída | `spatie/laravel-pdf` / Browsershot | Renderização de PDFs de alta fidelidade a partir de templates HTML/Tailwind via Chromium headless. |
| Provedor de IA | **OpenAI / Anthropic** via `LlmProviderInterface` | Abstração através do Adapter Pattern (`OpenAiAdapter`, `AnthropicAdapter`). |
| Autenticação | **Laravel Sanctum** | Tokens de acesso SPA / API Bearer Tokens stateless. |
| Gestão de Assinaturas | **Laravel Cashier / Stripe** | Gestão de checkout, planos e webhooks. |
| Observabilidade | **Sentry** | Rastreamento de exceptions em requisições HTTP e Jobs do Horizon. |
| Documentação API | **OpenAPI / Scramble** (`dedoc/scramble`) | Gerado automaticamente a partir de FormRequests e Resources em `/api/documentation`. |
| Deploy & Infraestrutura | **Laravel Forge + VPS** | Automação de deploy com zero downtime. |

---

## 3. Princípios Arquiteturais e Padrões de Projeto

1. **API Versionada**: Todas as rotas de negócio residem sob o prefixo `/api/v1`.
2. **Stateless & Bearer Authentication**: Autenticação via tokens do Sanctum enviados no header `Authorization: Bearer <token>`.
3. **Assincronismo Obrigatório para Operações Longas**: Upload/extração de PDF, geração por IA e compilação de PDF de saída **não podem bloquear requisições HTTP**. Operações assíncronas retornam `HTTP 202 Accepted` com ID e status URL para Polling.
4. **Adapter Pattern para LLMs (`LlmProviderInterface`)**: Regras de negócio consomem a interface `App\Services\Ai\Contracts\LlmProviderInterface`. O driver ativo é configurado via `.env` (`LLM_DRIVER=openai` ou `anthropic`).
5. **Action Pattern & Thin Controllers**: Controllers possuem responsabilidade única de orquestração HTTP. Regras de negócio residem em `Actions` e `Services`.
6. **Autorização Obrigatória por Ownership (Policies)**: 100% dos recursos (`Document`, `QuestionBatch`, `Question`, `Export`) têm acesso validado via Laravel Policies.
7. **Storage Privado com Presigned URLs**: Nenhum arquivo enviado ou exportado possui URL pública direta. Downloads utilizam Signed URLs temporárias com TTL padrão de 60 minutos.
8. **Resiliência e Idempotência nas Filas**: Jobs utilizam retries com backoff exponencial, locks atômicos do Redis para evitar duplicidade de execução e graceful shutdown (`SIGTERM`).
9. **Contrato Único de Resposta de Erro**: Todas as exceções tratadas ou não tratadas retornam o formato JSON padronizado.

---

## 4. Arquitetura de Camadas (Laravel Layering)

```text
               +----------------------------------+
               |        Cliente (Web / App)        |
               +----------------------------------+
                                |  HTTP Request (JSON / Multipart)
                                v
               +----------------------------------+
               |        FormRequest / Policy      |
               | (Validação e Autorização de DTO)  |
               +----------------------------------+
                                |
                                v
               +----------------------------------+
               |    Single Action Controller      |
               |     (Orquestração HTTP 200/202)  |
               +----------------------------------+
                  /                            \
                 / (Operações rápidas)          \ (Operações demoradas)
                v                                v
   +-------------------------+       +-------------------------+
   |     Business Action     |       |    Horizon Queue Job    |
   |   (Ex: UpdateQuestion)  |       | (GenerateQuestionsJob)  |
   +-------------------------+       +-------------------------+
                |                                |
                v                                v
   +-------------------------+       +-------------------------+
   |   Eloquent Data Model   |       |  LlmProviderInterface   |
   |   (Database/PostgreSQL) |       |   (OpenAI / Anthropic)  |
   +-------------------------+       +-------------------------+
                \                                /
                 \                              /
                  v                            v
               +----------------------------------+
               |           API Resource           |
               | (Formatação Padronizada de JSON) |
               +----------------------------------+
```

---

## 5. Fluxo Macro do Sistema

```text
React / Web App
   |
   v
Laravel API (/api/v1)
   |
   +-- Auth (Sanctum) --------------------------> PostgreSQL (users)
   |
   +-- Documents (POST /documents) -------------> S3 / Cloudflare R2 (Private)
   |      |
   |      +-- [Job: ExtractTextFromDocument] ----> smalot/pdfparser -> DB (documents.extracted_text)
   |
   +-- Question Batches (POST /batches) --------> Redis Lock (Check quota)
   |      |
   |      +-- [Job: GenerateQuestionsJob] ------> LlmProviderInterface (OpenAI/Anthropic)
   |                                                    | (Retries 3x, timeout 120s)
   |                                                    v
   |                                              Validação Schema & Persistência (questions)
   |                                                    |
   |                                                    v
   |                                              Incrementa uso do mês (users)
   |
   +-- Questions (PATCH /questions/{id}) -------> DB (questions.status = edited)
   |
   +-- Exports (POST /batches/{id}/export) -----> [Job: ExportBatchToPdf]
   |                                                    | (spatie/laravel-pdf)
   |                                                    v
   |                                              S3 / R2 -> Retorna Signed URL (TTL 60m)
   |
   +-- Billing & Webhooks ----------------------> Cashier / Stripe Gateway
```

---

## 6. Filas, Resiliência e Worker Management

O processamento assíncrono é subdividido em 4 filas dedicadas gerenciadas pelo Laravel Horizon:

| Fila | Finalidade | Timeout | Tentativas (Retries) | Backoff |
|---|---|---|---|---|
| `documents` | Extração de texto de PDFs | 60s | 2 | 5s, 15s |
| `generation` | Chamadas para LLM e parsing de questões | 120s | 3 | Exponencial: 10s, 30s, 60s |
| `exports` | Renderização do template e compilação em PDF | 90s | 2 | 10s, 30s |
| `billing` | Webhooks e sincronização de assinaturas | 30s | 3 | 5s, 15s, 30s |

### Polling e Estados do Processamento
Operações assíncronas alteram o estado do recurso e são consultadas pelo cliente via HTTP Polling a cada 2–3 segundos:

- **Document**: `pending` → `processing` → `extracted` | `failed`
- **QuestionBatch**: `processing` → `completed` | `partial` | `failed`
- **Export**: `processing` → `completed` | `failed`

### Dead Letter Queue (DLQ) & Graceful Shutdown
- Jobs que falharem após todas as tentativas são movidos para a tabela `failed_jobs` e uma exceção de alta prioridade é enviada ao Sentry.
- O Horizon é configurado para interceptar o sinal `SIGTERM` e aguardar a finalização dos jobs ativos antes de desligar os workers durante deploys (`php artisan horizon:terminate`).

---

## 7. Modelo de Dados Macro (Relacionamentos Principais)

Todas as chaves primárias utilizam **UUID v4** (gerados na aplicação via `Str::uuid()`).

```text
+-----------------------+       1:N       +-----------------------+
|        users          |---------------->|       documents       |
+-----------------------+                 +-----------------------+
| id (UUID)             |                             |
| name, email, password |                             | 1:N
| plan (free|pro)       |                             v
| questions_generated   |                 +-----------------------+
+-----------------------+---------------->|    question_batches   |
          |       1:N                     +-----------------------+
          |                               | id (UUID)             |
          |                               | document_id (FK)      |
          |                               | status, diff, area    |
          |                               +-----------------------+
          |                                           |
          |                                           | 1:N
          |                                           v
          |                               +-----------------------+
          |                               |       questions       |
          |                               +-----------------------+
          |                               | id (UUID)             |
          |                               | batch_id (FK)         |
          |                               | statement, alts (JSON)|
          |                               | correct_alt, status   |
          |                               +-----------------------+
          |
          v 1:N
+-----------------------+
|        exports        |
+-----------------------+
| id (UUID)             |
| batch_id (FK)         |
| storage_path, status  |
+-----------------------+
```

---

## 8. Abstração do Provedor de IA (`LlmProviderInterface`)

Para evitar acoplamento a um único fornecedor (OpenAI vs Anthropic), a integração é intermediada pela interface:

```php
namespace App\Services\Ai\Contracts;

use App\Services\Ai\DTOs\PromptPayload;
use App\Services\Ai\DTOs\GeneratedQuestionsResult;

interface LlmProviderInterface
{
    /**
     * Envia o prompt e o texto extraído para o provedor de IA e retorna o resultado estruturado.
     *
     * @throws \App\Services\Ai\Exceptions\LlmProviderException
     */
    public function generateQuestions(PromptPayload $payload): GeneratedQuestionsResult;
}
```

### Implementações
- `App\Services\Ai\Adapters\OpenAiAdapter`: Utiliza `Illuminate\Http\Client` com chamadas a `gpt-4o` / `gpt-4o-mini` via Structured Outputs / Function Calling.
- `App\Services\Ai\Adapters\AnthropicAdapter`: Utiliza `Illuminate\Http\Client` com chamadas a `claude-3-5-sonnet` / `claude-3-haiku` via Tool Use / JSON mode.

O container de injeção de dependência vincula `LlmProviderInterface` com base no arquivo `config/ai.php`:

```php
$this->app->bind(LlmProviderInterface::class, function () {
    return match (config('ai.default_driver')) {
        'anthropic' => new AnthropicAdapter(config('ai.anthropic.key')),
        default => new OpenAiAdapter(config('ai.openai.key')),
    };
});
```

---

## 9. Throttling, Limites de Payload & Concorrência

### Limites Globais de Payload HTTP
- Payload máximo da requisição HTTP: **25 MB**.
- Tamanho máximo do PDF para upload: **20 MB** por arquivo (`mimes:pdf`).

### Matriz de Rate Limiting (Throttling)
Configurado no `RouteServiceProvider` / `AppServiceProvider` utilizando `RateLimiter`:

| Rota / Ação | Limite | Identificador |
|---|---|---|
| Autenticação (`/auth/login`, `/auth/register`) | `10 req/min` | IP do cliente |
| Global Autenticado (`/api/v1/*`) | `60 req/min` | ID do Usuário |
| Upload de PDF (`POST /documents`) | `10 req/min` | ID do Usuário |
| Geração de Lotes (`POST /question-batches`) | `5 req/min` | ID do Usuário |
| Exportação de PDF (`POST /batches/{id}/export`) | `10 req/min` | ID do Usuário |

### Controle de Concorrência e Dedução de Franquia
Para evitar que requisições simultâneas excedam a franquia do plano do usuário (`users.questions_generated_this_month`):

1. O endpoint `POST /question-batches` obtém um lock atômico no Redis baseado no ID do usuário:
   ```php
   Cache::lock('user-generation-lock:' . $user->id, 10)->block(3, function () use ($user, $requestedCount) {
       if (($user->questions_generated_this_month + $requestedCount) > $user->planLimit()) {
           throw new PlanLimitExceededException();
       }
   });
   ```
2. A franquia **só é incrementada após a conclusão bem-sucedida do Job** `GenerateQuestionsJob`, garantindo que falhas na API de IA não penalizem o usuário.

---

## 10. Contrato Único de Resposta de Erro

Todas as respostas de erro HTTP da API (`4xx` e `5xx`) seguem estritamente a mesma estrutura JSON:

```json
{
  "error": {
    "code": "ERROR_CODE",
    "message": "Mensagem descritiva e amigável para o usuário.",
    "details": {}
  }
}
```

### Exemplo de Erro de Validação (`422 Unprocessable Content`):
```json
{
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Os dados fornecidos são inválidos.",
    "details": {
      "requested_count": [
        "A quantidade de questões deve estar entre 1 e 20."
      ],
      "knowledge_area": [
        "A área do conhecimento selecionada é inválida."
      ]
    }
  }
}
```

### Exemplo de Limite Excedido (`403 Forbidden`):
```json
{
  "error": {
    "code": "PLAN_LIMIT_EXCEEDED",
    "message": "Você atingiu o limite de questões do seu plano este mês.",
    "details": {
      "limit": 10,
      "used": 10,
      "requested": 5
    }
  }
}
```

### Códigos de Erro Padronizados
- `VALIDATION_ERROR` (HTTP 422)
- `UNAUTHORIZED` (HTTP 401)
- `FORBIDDEN` (HTTP 403)
- `NOT_FOUND` (HTTP 404)
- `PLAN_LIMIT_EXCEEDED` (HTTP 403)
- `DOCUMENT_EXTRACTION_FAILED` (HTTP 422/500)
- `GENERATION_FAILED` (HTTP 500)
- `RATE_LIMITED` (HTTP 429)

---

## 11. Segurança e Isolamento de Dados

1. **Validação de PDFs**:
   - Validação por Magic Bytes (`finfo_file`) garantindo que o arquivo é um PDF genuíno.
   - Bloqueio de arquivos com extensão `.pdf` falsificada.
2. **Sanitização de Texto Extraído**:
   - O texto extraído do PDF passa por sanitização via Regex para remoção de caracteres nulos, símbolos de controle UTF-8 inválidos e tentativas ingênuas de prompt injection antes de ser enviado à LLM.
3. **Isolamento de Tenant (Ownership)**:
   - Toda consulta no banco é protegida por `Policies` que verificam se o recurso pertence ao `auth()->user()`.
4. **Gestão de Secrets**:
   - Chaves de API (`OPENAI_API_KEY`, `ANTHROPIC_API_KEY`, `STRIPE_SECRET`, `SENTRY_DSN`) residem exclusivamente no `.env` do servidor e no Laravel Forge. Nunca são expostas na resposta da API ou no frontend.

---

## 12. Observabilidade, Health Check e Documentação

### Health Check Endpoint
Endpoint público para monitoramento de infraestrutura:
`GET /api/v1/health`

Resposta `200 OK`:
```json
{
  "status": "ok",
  "timestamp": "2026-08-11T12:00:00Z",
  "services": {
    "database": "ok",
    "redis": "ok",
    "storage": "ok"
  }
}
```
Caso algum serviço crítico falhe, retorna `503 Service Unavailable`.

### Logging Estruturado (JSON Format)
Logs de backend são emitidos em formato JSON estruturado incluindo contexto contextual:
```json
{
  "timestamp": "2026-08-11T12:00:00Z",
  "level": "info",
  "event": "question_batch_generated",
  "user_id": "uuid-user-123",
  "batch_id": "uuid-batch-456",
  "requested_count": 10,
  "generated_count": 10,
  "tokens_used": 4850,
  "cost_estimate_usd": 0.0145,
  "duration_ms": 3420
}
```

### Documentação da API (OpenAPI / Swagger)
A API é documentada automaticamente pelo **Scramble** em `/api/documentation` nos ambientes `local` e `staging`. O acesso é desabilitado em `production`.

---

## 13. Ambientes e Pipeline de Deployment

- **local**: Docker Compose (Laravel Sail) contendo PHP 8.3, PostgreSQL 16, Redis 7 e Mailpit.
- **staging**: VPS gerenciada pelo Laravel Forge para testes automatizados e homologação.
- **production**: VPS de alta performance provisionada pelo Laravel Forge. Deploys executam automaticamente migrações de banco zerodowntime (`php artisan migrate --force`) e reinício gracioso de workers (`php artisan horizon:terminate`).

