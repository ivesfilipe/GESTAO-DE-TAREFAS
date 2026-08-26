# ADR-015 — Memória Gerencial Persistente com Retrieval Lexical Local

**Status**: Aceito  
**Data**: 2026-08-26

## Contexto
O Copiloto do Gestor precisa de "memória" sobre liderados: perfis comportamentais, documentos (currículos, certificações, anotações) e conhecimentos derivados. Essa memória deve ser persistente, recuperável e acessível apenas ao gestor.

## Decisão
1. Criar quatro tabelas de memória gerencial:
   - `team_member_profiles` — perfil do liderado (resumo, pontos fortes, gaps, preferências, etc.).
   - `team_member_documents` — documentos anexados (nome, caminho, texto extraído, metadados).
   - `team_member_knowledge_chunks` — chunks textuais para retrieval lexical.
   - `ai_usage_logs` — auditoria de chamadas à IA (provider, modelo, tokens, custo, status).
2. Implementar `TeamKnowledgeService` responsável por:
   - Receber documentos e extrair texto via `DocumentTextExtractor`.
   - Fazer chunking simples (por parágrafos/tamanho máximo) e armazenar chunks.
   - Realizar retrieval lexical local por `LIKE` sobre o conteúdo dos chunks (sem vector DB externo).
3. O acesso à memória é restrito ao gestor (`$user->isGestor()`), garantido por policies.
4. Dados de memória só são enviados a APIs externas quando a camada `ZeroDataRetention` permitir e o gestor confirmar o contexto.

## Motivo
- Não adiciona dependência de infraestrutura externa ( Pinecone, Weaviate, etc.).
- SQLite/MySQL padrão do projeto já suporta busca textual simples com bom desempenho para o volume esperado.
- Permite evolução futura para full-text search ou vector DB sem mudar contratos de serviço.

## Alternativas descartadas
- Vector DB externo (Pinecone/Weaviate): adicionaria custo, latência e ponto de falha externo.
- Armazenar documentos como BLOB no banco: dificulta retrieval e aumenta tamanho do banco SQLite.
- Não ter memória: reduziria drasticamente a utilidade do copiloto.

## Consequências
- Todo documento precisa passar por `DocumentTextExtractor` antes de ser indexado.
- Retrieval lexical é menos sofisticado que semântico, mas suficiente para o volume e linguagem específica do domínio.
- Perfis e documentos devem ser criados/atualizados por controllers com confirmação humana; a IA nunca escreve diretamente.
