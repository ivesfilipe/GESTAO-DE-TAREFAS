# ADR-014 — Arquitetura Multi-Provider de IA com Groq como Padrão

**Status**: Aceito  
**Data**: 2026-08-26

## Contexto
O projeto já possui um `AiAssistantService` acoplado à API da OpenAI. Precisamos de uma camada de IA mais flexível, econômica e alinhada à política de dados da MedicalThermo. A Groq oferece latência baixa e preços competitivos, mas precisamos manter a possibilidade de usar OpenAI ou Ollama local sem reescrever código.

## Decisão
1. Criar uma camada de abstração em `app/Services/AI/` composta por:
   - `Contracts/AI/AIProviderInterface` — contrato único para qualquer provedor.
   - `DTO/AI/AIRequest` e `DTO/AI/AIResponse` — troca tipada de mensagens.
   - `AIProviderManager` — resolve o provider ativo via `config('ai.default')`.
   - Providers concretos: `GroqProvider`, `OpenAIProvider`, `OllamaProvider`.
   - `AIService` — fachada com alta taxa de reutilização.
2. Provider padrão será **Groq** (`GROQ_BASE_URL=https://api.groq.com/openai/v1`, modelo padrão `openai/gpt-oss-120b`).
3. **Nenhum fallback automático para OpenAI** quando Groq falha. A configuração `AI_FALLBACK_ENABLED` deve ser explicitamente `true` para que fallback seja considerado, e mesmo assim será logado e auditável.
4. Se `GROQ_API_KEY` estiver ausente, o sistema entra em modo **mock/simulação controlada** (respostas determinísticas e seguras), nunca chamando API paga.
5. Implementar `ZeroDataRetention` em `app/Services/AI/Safety/ZeroDataRetention.php` para anonimizar/bloquear dados reais de colaboradores, tarefas e documentos enquanto `GROQ_ZDR_CONFIRMED=false`.
6. Registrar configurações em `config/ai.php`, com variáveis de ambiente em `.env.example`.

## Motivo
- Reduz custo e dependência de único vendor.
- Permite rodar local/off-line com Ollama ou mock.
- Garante conformidade com política de retenção de dados antes de enviar qualquer informação real a APIs externas.
- Facilita testes: provider pode ser trocado por mock sem alterar consumidores.

## Alternativas descartadas
- Manter acoplamento direto à OpenAI: mantinha vendor lock-in e custo imprevisível.
- Criar fallback automático para OpenAI em caso de falha da Groq: viola a regra de não gastar créditos pagos sem consentimento explícito.
- Enviar dados reais sem camada ZDR: risco de vazamento de PII/informações corporativas.

## Consequências
- Todo consumidor de IA deve usar `AIService` ou `AIProviderManager`, nunca HTTP direto.
- `AiAssistantService` existente será preservado e pode delegar para a nova camada; não será removido nesta fase.
- Logs de uso ficam em `AIUsageLog` para auditoria.
