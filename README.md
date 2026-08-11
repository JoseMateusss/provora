# Provora — Gerador de Questões ENEM via IA

[![Laravel 11](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat&logo=php)](https://php.net)
[![Tests Status](https://img.shields.io/badge/Tests-Passing-brightgreen?style=flat)](#testes-e-qualidade)
[![API Documentation](https://img.shields.io/badge/API-OpenAPI%20%2F%20Scramble-blue?style=flat)](#documentação-da-api)

**Provora** é uma plataforma SaaS voltada para professores de cursinhos e preparatórios do ENEM. A plataforma utiliza Inteligência Artificial para transformar materiais didáticos próprios em formato PDF em lotes de questões de múltipla escolha no estilo ENEM, com 5 alternativas e gabarito comentado.

---

## 🎯 Escopo do MVP

- **Autenticação e Gestão de Conta**: Cadastro, login e controle de sessão stateless via Bearer Tokens (Laravel Sanctum).
- **Upload & Extração de PDFs**: Leitura e extração de texto de documentos PDF pesquisáveis.
- **Geração de Questões via IA**: Criação de lotes (1 a 20 questões) filtrados por área do conhecimento (Linguagens, Humanas, Natureza, Matemática) e dificuldade (Fácil, Médio, Difícil).
- **Abstração Multiprovedor de LLM**: Suporte alternável a OpenAI (`gpt-4o-mini`) e Anthropic (`claude-3-5-sonnet`) via Adapter Pattern.
- **Revisão e Edição**: Edição individual de enunciados, alternativas e exclusão lógica de questões.
- **Exportação para PDF**: Geração de PDFs diagramados prontos para impressão.
- **Gestão de Assinaturas & Franquias**: Controle de limites mensais de geração por plano (Free / Pro) via Laravel Cashier (Stripe).

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| **Framework** | Laravel 11.x |
| **Linguagem** | PHP 8.4+ |
| **Banco de Dados** | PostgreSQL 16 / SQLite (Dev) |
| **Filas & Cache** | Redis + Predis + Laravel Horizon |
| **Autenticação** | Laravel Sanctum |
| **Documentação API** | Dedoc Scramble (OpenAPI / Swagger) |
| **Gestão de Planos** | Laravel Cashier (Stripe) |
| **Extração de PDF** | `smalot/pdfparser` |
| **Exportação de PDF** | `spatie/laravel-pdf` |

---

## 🚀 Como Rodar o Projeto Localmente

### Pré-requisitos
- PHP >= 8.3 (recomendado PHP 8.4)
- Composer >= 2.x
- Redis Server (opcional para filas locais)

### Passo a Passo

1. **Clonar o Repositório e Instalar Dependências**:
   ```bash
   composer install
   ```

2. **Configurar as Variáveis de Ambiente**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Executar as Migrations do Banco de Dados**:
   ```bash
   php artisan migrate
   ```

4. **Iniciar o Servidor de Desenvolvimento**:
   ```bash
   php artisan serve
   ```
   A aplicação estará disponível em: `http://127.0.0.1:8000`

---

## 📖 Documentação da API

Com o servidor rodando (`php artisan serve`), acesse a documentação interativa OpenAPI gerada automaticamente:

- **UI da Documentação**: [http://127.0.0.1:8000/api/documentation](http://127.0.0.1:8000/api/documentation)
- **JSON OpenAPI**: [http://127.0.0.1:8000/docs/api.json](http://127.0.0.1:8000/docs/api.json)

---

## 🧪 Testes e Qualidade

Para rodar a suíte de testes unitários e de integração:

```bash
php artisan test
```

### Cobertura de Testes Atual:
- ✅ **Health Check**: Validação do status dos serviços (`GET /api/v1/health`).
- ✅ **Contrato de Erro JSON**: Garantia de padrão estruturado em erros HTTP 404, 422, 403.
- ✅ **LLM Adapter**: Resolução dinâmica entre OpenAI e Anthropic no container do Laravel.
- ✅ **Documentação API**: Redirecionamento e acesso em ambiente local/staging.

---

## ⚡ Processamento de Filas (Horizon)

Para gerenciar e acompanhar o processamento assíncrono de PDFs e geração de IA:

```bash
php artisan horizon
```
Acesse o Dashboard do Horizon em: `http://127.0.0.1:8000/horizon`

---

## 📂 Estrutura de Especificações do Projeto

Toda a documentação arquitetural e de requisitos encontra-se em `/specs`:

- [`PRD.md`](PRD.md): Documento de Requisitos do Produto (MVP).
- [`specs/00-architecture.md`](specs/00-architecture.md): Arquitetura Técnica Backend.
- [`specs/01-auth.md`](specs/01-auth.md): Especificação de Autenticação.
- [`specs/02-documents.md`](specs/02-documents.md): Processamento de PDFs.
- [`specs/03-question-generation.md`](specs/03-question-generation.md): Geração de Questões via IA.
- [`specs/04-questions.md`](specs/04-questions.md): Gestão de Questões.
- [`specs/05-export.md`](specs/05-export.md): Exportação em PDF.
- [`specs/06-billing-and-usage.md`](specs/06-billing-and-usage.md): Cobrança e Franquias.
- [`specs/07-api-errors-security-observability.md`](specs/07-api-errors-security-observability.md): Erros, Segurança e Observabilidade.
- [`specs/08-testing-and-definition-of-done.md`](specs/08-testing-and-definition-of-done.md): Definição de Pronto e Testes.

---

## 📄 Licença

Este projeto é de propriedade privada e de uso exclusivo da plataforma **Provora**.
