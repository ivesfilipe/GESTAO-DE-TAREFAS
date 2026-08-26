# Serviços de Negócio da IA

## AIService

Fachada principal para chamadas de IA.

```php
$ai = app(AIService::class);
$response = $ai->ask(
    system: 'Você é um assistente.',
    user: 'Analise esta tarefa.',
    temperature: 0.5,
    maxTokens: 500,
    entities: $zdr->entitiesFromTask($task),
);
```

## ManagementRadarService

Resumo de risco do time para o gestor.

```php
$radar = app(ManagementRadarService::class);
$data = $radar->radar($gestor);
// $data['metrics'], $data['workload'], $data['summary']
```

## DelegationRecommendationService

Sugestão de delegação para uma tarefa.

```php
$service = app(DelegationRecommendationService::class);
$suggestion = $service->recommend($gestor, $title, $description, $priority);
// $suggestion['suggested_assignee_id'], $suggestion['task_type'], ...
```

## TeamPerformanceService

Métricas de performance e carga.

```php
$service = app(TeamPerformanceService::class);
$metrics = $service->memberMetrics($liderado);
$workload = $service->workloadDistribution();
```

## TeamKnowledgeService

Memória gerencial: documentos e chunks.

```php
$service = app(TeamKnowledgeService::class);
$document = $service->storeDocument($liderado, $uploadedFile);
$chunks = $service->retrieve($liderado, 'Laravel');
```

## SmartDelegationService

Gera rascunho completo de tarefa a partir de texto livre, com structured output JSON.

```php
$service = app(SmartDelegationService::class);
$draft = $service->draft($gestor, $text, $preselectedAssigneeId);
// $draft['title'], $draft['task_type'], $draft['recommended_assignee_id'], ...
```

## CopilotService

Chat do gestor com ferramentas (tools) para consultar tarefas e perfis, além de rascunho de cobrança.

```php
$copilot = app(CopilotService::class);
$answer = $copilot->ask($gestor, 'O que está atrasado?');
$draft = $copilot->suggestCollection($gestor, $task);
```

## ProfileIntelligenceService

Gera/atualiza o resumo inteligente do perfil profissional do liderado.

```php
$service = app(ProfileIntelligenceService::class);
$profile = $service->updateIntelligence($liderado);
```

## TaskSuggestionService

Sugere tarefas para o liderado com base no perfil, responsabilidades e métricas.

```php
$service = app(TaskSuggestionService::class);
$suggestions = $service->suggest($liderado, $category);
```

## Regra importante

Todos os serviços retornam **rascunhos**. A ação final deve ser executada por controllers/actions existentes com confirmação do gestor.
