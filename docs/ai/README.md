# Documentação da IA — Copiloto do Gestor

Esta pasta reúne a documentação técnica da camada de inteligência artificial do projeto.

## Arquivos

- [`configuracao.md`](configuracao.md) — Como configurar providers (Groq, OpenAI, Ollama) e ZDR.
- [`arquitetura.md`](arquitetura.md) — Visão geral da camada multi-provider.
- [`seguranca.md`](seguranca.md) — Zero Data Retention e governança de dados.
- [`servicos.md`](servicos.md) — Como usar os serviços de negócio.

## Regras de ouro

1. **Provider padrão é Groq** (`openai/gpt-oss-120b`).
2. **Nenhum fallback pago automático** — `AI_FALLBACK_ENABLED=false`.
3. **ZDR ativo por padrão** — dados reais só saem quando `GROQ_ZDR_CONFIRMED=true`.
4. **Sem escrita automática** — toda ação da IA é rascunho/recomendação; confirmação humana é obrigatória.
5. **Sem vector DB externo** — retrieval lexical local em SQLite/MySQL.
