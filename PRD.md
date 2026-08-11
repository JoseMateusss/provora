# PRD — Provora (MVP)

**Versão:** 1.0  
**Data:** 11/08/2026  
**Produto:** Provora  
**Escopo:** MVP

---

## 1. Visão do Produto

**Provora** é uma plataforma de geração de questões e avaliações com inteligência artificial.

No MVP, a Provora será focada em professores de cursinhos e preparatórios para o ENEM, permitindo transformar PDFs e materiais didáticos próprios em questões no estilo ENEM, prontas para revisão, edição e exportação.

A plataforma deve permitir que o professor faça upload de PDFs (apostilas, material de aula e resumos) e gere automaticamente, via IA, questões de múltipla escolha com 5 alternativas e gabarito comentado.

## 2. Problema

Professores gastam tempo desproporcional criando questões de qualidade, no estilo e nível de dificuldade correto do ENEM, a partir do conteúdo que já lecionam.

O produto busca oferecer uma ferramenta acessível, em português, focada especificamente no estilo ENEM, capaz de gerar questões com qualidade suficiente para exigir apenas revisão leve.

## 3. Público-alvo do MVP

### Dentro do público-alvo

- Professores de cursinhos preparatórios para o ENEM.
- Professores autônomos que preparam material próprio para alunos de ensino médio e vestibulandos.

### Fora do público-alvo no MVP

- Escolas regulares de ensino fundamental.
- Preparação para concursos públicos, como CESPE e FGV.
- Bancas específicas de vestibulares, como FUVEST e UNICAMP.

Esses públicos poderão ser considerados em fases posteriores.

## 4. Objetivo do MVP

Validar se professores usam e voltam a usar uma ferramenta que gera questões estilo ENEM a partir de PDFs próprios, com qualidade suficiente para exigir apenas revisão leve, e não reescrita completa.

## 5. Escopo funcional do MVP

### Incluído

- Cadastro e login de professor com e-mail e senha.
- Upload de PDF.
- Extração de texto de PDFs pesquisáveis.
- Configuração da geração:
  - Quantidade de questões: 1 a 20 por lote.
  - Nível de dificuldade: fácil, médio ou difícil.
  - Área do conhecimento:
    - Linguagens.
    - Ciências Humanas.
    - Ciências da Natureza.
    - Matemática.
- Geração de questões via IA no formato ENEM.
- Cada questão deve possuir:
  - Enunciado contextualizado.
  - Cinco alternativas.
  - Uma única alternativa correta.
  - Gabarito comentado.
- Acompanhamento do status da geração.
- Listagem e visualização das questões.
- Edição manual.
- Exclusão individual de questões.
- Exportação do lote revisado em PDF.
- Plano gratuito limitado por quantidade mensal de questões.
- Plano pago por assinatura mensal.

### Nice to have

- OCR básico para PDFs escaneados.

### Fora do escopo

- OCR robusto para PDFs escaneados de baixa qualidade.
- Estilos de outras bancas.
- Banco de questões compartilhado.
- Aplicação de provas online para alunos.
- Aplicativo mobile nativo.
- Múltiplos usuários por conta.

## 6. User Stories

1. Como professor, quero me cadastrar e fazer login para acessar minha área de trabalho.
2. Como professor, quero fazer upload de um PDF para que a IA use esse conteúdo como base das questões.
3. Como professor, quero escolher quantas questões gerar, o nível de dificuldade e a área do conhecimento.
4. Como professor, quero acompanhar o status da geração enquanto ela está sendo processada.
5. Como professor, quero revisar cada questão gerada e editar enunciado, alternativas e gabarito.
6. Como professor, quero excluir questões que não fizeram sentido.
7. Como professor, quero exportar o lote final revisado em PDF pronto para impressão.
8. Como professor, quero acompanhar quantas questões já gerei no mês e meu limite atual.
9. Como professor, quero assinar um plano pago para gerar mais questões e desbloquear recursos extras.

## 7. Jornada principal do usuário

1. Professor cria sua conta.
2. Faz login.
3. Envia um PDF.
4. Aguarda o processamento do documento.
5. Escolhe:
   - Documento.
   - Área do conhecimento.
   - Dificuldade.
   - Quantidade.
6. Solicita a geração.
7. Aguarda o processamento.
8. Revisa as questões.
9. Edita ou exclui questões quando necessário.
10. Exporta o lote final para PDF.

## 8. Regras de produto

### RP-01 — Questões são rascunhos

Toda questão gerada deve ser tratada como rascunho até a revisão do professor.

### RP-02 — Quantidade por lote

Um lote pode solicitar entre 1 e 20 questões.

### RP-03 — Limite de plano

A geração deve respeitar o limite mensal do plano do usuário.

### RP-04 — Cobrança de uso

Questões só devem consumir a franquia do usuário depois de uma geração confirmada com sucesso.

### RP-05 — Fonte do conteúdo

A IA deve usar como base o conteúdo extraído do documento enviado pelo usuário.

### RP-06 — Responsabilidade sobre o material

O usuário é responsável por possuir direito de uso sobre o conteúdo enviado.

Essa condição deve constar nos Termos de Uso.

## 9. Estados importantes para a experiência

### Documento

- pending
- processing
- extracted
- failed

### Lote de questões

- processing
- completed
- partial
- failed

### Questão

- draft
- edited
- approved
- deleted

### Exportação

- processing
- completed
- failed

## 10. Métricas de sucesso

### Ativação

Percentual de usuários cadastrados que geram pelo menos um lote de questões na primeira semana.

### Retenção

Percentual de usuários que geram um segundo lote em até 30 dias.

### Qualidade percebida

Percentual de questões geradas que são exportadas sem edição.

### Conversão

Percentual de usuários do plano gratuito que migram para o plano pago em até 60 dias.

## 11. Premissas e restrições

- O custo da API de IA varia conforme o uso.
- O modelo de planos deve controlar a quantidade de questões geradas.
- O usuário é responsável pelos direitos de uso dos PDFs enviados.
- Questões produzidas pela IA precisam de revisão humana.
- O MVP será API + aplicação web responsiva.
- O backend deve permanecer adequado para consumo futuro por aplicativo mobile.

## 12. Critério geral de sucesso do MVP

O MVP será considerado validado caso seja possível observar que professores:

1. Conseguem transformar material próprio em questões.
2. Consideram a qualidade suficiente para revisar, em vez de reescrever.
3. Voltam a utilizar o produto.
4. Demonstram disposição para migrar do plano gratuito para um plano pago.

## 13. Roadmap pós-MVP

- OCR robusto.
- Outras bancas e estilos de prova.
- Banco de questões compartilhado.
- WebSockets para atualizações em tempo real.
- Contas multiusuário.
- Aplicativo mobile nativo.
