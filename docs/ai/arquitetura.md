# Arquitetura da Camada de IA

## Componentes

```
app/
├── Contracts/AI/AIProviderInterface.php
├── DTO/AI/AIRequest.php
├── DTO/AI/AIResponse.php
└── Services/AI/
    ├── AIService.php                 # Fachada
    ├── AIProviderManager.php         # Resolve provider ativo
    ├── Providers/
    │   ├── GroqProvider.php
    │   ├── OpenAIProvider.php
    │   ├── OllamaProvider.php
    │   └── MockProvider.php
    ├── Safety/ZeroDataRetention.php
    ├── Prompts/ManagementPrompts.php
    ├── Tools/AITools.php
    ├── DocumentTextExtractor.php
    ├── TeamKnowledgeService.php
    ├── ManagementRadarService.php
    ├── DelegationRecommendationService.php
    ├── SmartDelegationService.php       # Delegação com structured output
    ├── CopilotService.php               # Chat do gestor com tools
    ├── ProfileIntelligenceService.php   # Resumo inteligente do liderado
    ├── TaskSuggestionService.php        # Sugestões de tarefas por perfil
    └── TeamPerformanceService.php
```

## Fluxo de uma chamada

1. Controller ou serviço de negócio chama `AIService::ask()`.
2. `AIService` aplica `ZeroDataRetention` se necessário.
3. Provider ativo (`AIProviderManager::resolve()`) executa a chamada.
4. Resposta é registrada em `AIUsageLog`.
5. Serviço de negócio parseia a resposta (JSON/texto) e retorna rascunho.

## Contrato do provider

Todo provider implementa:
- `name(): string`
- `isAvailable(): bool`
- `complete(AIRequest $request): AIResponse`

Providers compatíveis com OpenAI usam `OpenAICompatibleProvider` como base.
