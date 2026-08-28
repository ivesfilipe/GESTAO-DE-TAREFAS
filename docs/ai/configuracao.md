# Configuração da IA

## Variáveis de ambiente

```env
# Provedor padrão: groq | openai | ollama | mock
AI_PROVIDER=groq
AI_FALLBACK_ENABLED=false
AI_FALLBACK_CHAIN=ollama,mock

# Groq (padrão)
GROQ_BASE_URL=https://api.groq.com/openai/v1
GROQ_API_KEY=
GROQ_MODEL=openai/gpt-oss-120b
GROQ_VISION_MODEL=meta-llama/llama-4-scout-17b-16e-instruct
GROQ_TIMEOUT=30
GROQ_ZDR_REQUIRED=true
GROQ_ZDR_CONFIRMED=false

# OpenAI (opcional, nunca fallback automático)
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4o-mini

# Ollama (local)
OLLAMA_BASE_URL=http://localhost:11434/v1
OLLAMA_MODEL=llama3.1
OLLAMA_TIMEOUT=60

# Memória gerencial
AI_KNOWLEDGE_CHUNK_SIZE=800
AI_KNOWLEDGE_CHUNK_OVERLAP=80
AI_KNOWLEDGE_MAX_CHUNKS=5
AI_LOGGING_ENABLED=true
AI_MAX_TOOL_ITERATIONS=3
```

## Modos de operação

### 1. Groq com ZDR confirmado
- `GROQ_API_KEY` preenchido.
- `GROQ_ZDR_CONFIRMED=true`.
- Dados reais podem ser enviados (se apropriado e com consentimento documentado).

### 2. Groq com ZDR não confirmado
- `GROQ_API_KEY` preenchido.
- `GROQ_ZDR_CONFIRMED=false`.
- Nenhum contexto é enviado a Groq, OpenAI ou outro provider externo. A aplicação retorna indisponibilidade controlada e mantém apenas fluxos mock/Ollama local.

### 3. Mock/simulação
- `GROQ_API_KEY` ausente OU provider `mock`.
- Respostas determinísticas, sem chamada externa.
- Ideal para testes e desenvolvimento local.

### 4. Ollama local
- `OLLAMA_BASE_URL` apontando para instância local.
- Útil para execução totalmente off-line.
