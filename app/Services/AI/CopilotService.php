<?php

namespace App\Services\AI;

use App\Models\Task;
use App\Models\User;
use App\Services\AI\Prompts\ManagementPrompts;
use App\Services\AI\Safety\ZeroDataRetention;
use Illuminate\Support\Facades\Log;

class CopilotService
{
    private AIService $ai;

    private TeamPerformanceService $performance;

    private TeamKnowledgeService $knowledge;

    private ZeroDataRetention $zdr;

    private int $maxIterations;

    public function __construct(
        ?AIService $ai = null,
        ?TeamPerformanceService $performance = null,
        ?TeamKnowledgeService $knowledge = null,
        ?ZeroDataRetention $zdr = null,
    ) {
        $this->ai = $ai ?? app(AIService::class);
        $this->performance = $performance ?? new TeamPerformanceService;
        $this->knowledge = $knowledge ?? new TeamKnowledgeService;
        $this->zdr = $zdr ?? new ZeroDataRetention;
        $this->maxIterations = (int) config('ai.max_tool_iterations', 3);
    }

    /**
     * Responde uma pergunta do gestor usando tools para buscar dados reais.
     *
     * @return array<string, mixed>
     */
    public function answer(User $gestor, string $question): array
    {
        $messages = [
            ['role' => 'system', 'content' => ManagementPrompts::copilot()],
            ['role' => 'user', 'content' => $question],
        ];

        $iteration = 0;

        while ($iteration < $this->maxIterations) {
            $response = $this->ai->ask(
                system: $messages[0]['content'],
                user: $this->messagesToString($messages),
                temperature: 0.3,
                maxTokens: 900,
                entities: $this->entitiesForContext(),
                tools: $this->toolDefinitions(),
            );

            if ($response->toolCalls === []) {
                return [
                    'answer' => $response->content,
                    'provider' => $this->ai->provider()->name(),
                    'mock' => $this->ai->isMock(),
                    'iterations' => $iteration,
                ];
            }

            $messages[] = [
                'role' => 'assistant',
                'content' => $response->content,
                'tool_calls' => $response->toolCalls,
            ];

            foreach ($response->toolCalls as $call) {
                $result = $this->executeTool($gestor, $call['name'], $call['arguments'] ?? []);
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $call['id'] ?? '',
                    'name' => $call['name'],
                    'content' => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];
            }

            $iteration++;
        }

        return [
            'answer' => 'Não consegui obter todas as informações necessárias. Tente reformular a pergunta.',
            'provider' => $this->ai->provider()->name(),
            'mock' => $this->ai->isMock(),
            'iterations' => $iteration,
        ];
    }

    /**
     * Gera um rascunho de cobrança para uma tarefa.
     *
     * @return array<string, mixed>
     */
    public function suggestCollection(User $gestor, Task $task): array
    {
        $assignee = $task->assignee;
        $context = $this->buildTaskContext($task);

        $system = <<<'PROMPT'
Você é um assistente de gestão. Gere um rascunho de mensagem de cobrança objetiva e respeitosa.
A mensagem NUNCA deve ser enviada automaticamente; o gestor revisará antes.
Não inclua dados pessoais sensíveis. Use o nome do responsável apenas se for seguro.
Responda APENAS com o texto do rascunho.
PROMPT;

        $response = $this->ai->ask(
            system: $system,
            user: $context,
            temperature: 0.4,
            maxTokens: 500,
            entities: $assignee ? $this->zdr->entitiesFromUser($assignee) : [],
        );

        return [
            'draft' => $response->content,
            'provider' => $this->ai->provider()->name(),
            'mock' => $this->ai->isMock(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function toolDefinitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_overdue_tasks',
                    'description' => 'Lista tarefas atrasadas do time.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'description' => 'Máximo de resultados'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_tasks_due_today',
                    'description' => 'Lista tarefas que vencem hoje.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'description' => 'Máximo de resultados'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_blocked_tasks',
                    'description' => 'Lista tarefas bloqueadas.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'description' => 'Máximo de resultados'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_tasks_awaiting_approval',
                    'description' => 'Lista tarefas aguardando aprovação do gestor.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limit' => ['type' => 'integer', 'description' => 'Máximo de resultados'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_team_member_profile',
                    'description' => 'Retorna perfil e métricas de um liderado pelo nome.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Nome do liderado'],
                        ],
                        'required' => ['name'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_team_knowledge',
                    'description' => 'Busca documentos/chunks de um liderado sobre um tema.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'member_name' => ['type' => 'string', 'description' => 'Nome do liderado'],
                            'query' => ['type' => 'string', 'description' => 'Tema da busca'],
                            'limit' => ['type' => 'integer', 'description' => 'Máximo de chunks'],
                        ],
                        'required' => ['member_name', 'query'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return array<string, mixed>
     */
    private function executeTool(User $gestor, string $name, array $arguments): array
    {
        try {
            return match ($name) {
                'list_overdue_tasks' => $this->toolOverdueTasks($arguments['limit'] ?? 10),
                'list_tasks_due_today' => $this->toolTasksDueToday($arguments['limit'] ?? 10),
                'list_blocked_tasks' => $this->toolBlockedTasks($arguments['limit'] ?? 10),
                'list_tasks_awaiting_approval' => $this->toolAwaitingApproval($arguments['limit'] ?? 10),
                'get_team_member_profile' => $this->toolMemberProfile($arguments['name'] ?? ''),
                'search_team_knowledge' => $this->toolSearchKnowledge(
                    $arguments['member_name'] ?? '',
                    $arguments['query'] ?? '',
                    $arguments['limit'] ?? 3,
                ),
                default => ['error' => 'Tool desconhecida'],
            };
        } catch (\Throwable $e) {
            Log::warning('Copilot tool failed', ['tool' => $name, 'error' => $e->getMessage()]);

            return ['error' => 'Falha ao executar tool: '.$e->getMessage()];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function toolOverdueTasks(int $limit): array
    {
        return Task::overdue()->limit($limit)->get()->map(fn (Task $task) => [
            'id' => $task->id,
            'title' => $task->title,
            'priority' => $task->priority,
            'assignee_id' => $task->assigned_to,
            'due_at' => $task->due_at?->format('d/m/Y H:i'),
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function toolTasksDueToday(int $limit): array
    {
        return Task::query()
            ->whereDate('due_at', today())
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->limit($limit)
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'priority' => $task->priority,
                'assignee_id' => $task->assigned_to,
            ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function toolBlockedTasks(int $limit): array
    {
        return Task::where('status', 'bloqueada')->limit($limit)->get()->map(fn (Task $task) => [
            'id' => $task->id,
            'title' => $task->title,
            'assignee_id' => $task->assigned_to,
            'block_reason' => $task->block_reason,
        ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function toolAwaitingApproval(int $limit): array
    {
        return Task::where('status', 'aguardando_aprovacao')->limit($limit)->get()->map(fn (Task $task) => [
            'id' => $task->id,
            'title' => $task->title,
            'assignee_id' => $task->assigned_to,
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function toolMemberProfile(string $name): array
    {
        $member = User::where('role', 'liderado')
            ->where('is_active', true)
            ->where('name', 'like', "%{$name}%")
            ->first();

        if (! $member) {
            return ['error' => 'Liderado não encontrado'];
        }

        $profile = $member->teamProfile;
        $metrics = $this->performance->memberMetrics($member);

        return [
            'id' => $member->id,
            'name' => '[PESSOA_ANONIMA_'.$member->id.']',
            'role' => $profile?->role,
            'department' => $profile?->department,
            'responsibilities' => $profile?->responsibilities,
            'objectives' => $profile?->professional_objectives,
            'metrics' => $metrics,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toolSearchKnowledge(string $name, string $query, int $limit): array
    {
        $member = User::where('role', 'liderado')
            ->where('is_active', true)
            ->where('name', 'like', "%{$name}%")
            ->first();

        if (! $member) {
            return ['error' => 'Liderado não encontrado'];
        }

        $chunks = $this->knowledge->retrieve($member, $query, $limit);

        return [
            'member_id' => $member->id,
            'results' => $chunks->map(fn ($chunk) => [
                'document_name' => $chunk->document?->name,
                'content' => mb_substr($chunk->content, 0, 300),
            ])->all(),
        ];
    }

    private function buildTaskContext(Task $task): string
    {
        $lines = [
            'Tarefa: '.$task->title,
            'Prazo: '.($task->due_at ? $task->due_at->format('d/m/Y H:i') : 'não definido'),
            'Status: '.$task->status,
            'Prioridade: '.$task->priority,
        ];

        if ($task->description) {
            $lines[] = 'Descrição: '.$task->description;
        }

        if ($task->block_reason) {
            $lines[] = 'Bloqueio: '.$task->block_reason;
        }

        $recentComments = $task->comments()->latest()->limit(3)->get();
        if ($recentComments->isNotEmpty()) {
            $lines[] = 'Comentários recentes:';
            foreach ($recentComments as $comment) {
                $lines[] = '- '.mb_substr($comment->content, 0, 200);
            }
        }

        return implode("\n", $lines);
    }

    /**
     * @param  list<array<string, mixed>>  $messages
     */
    private function messagesToString(array $messages): string
    {
        $parts = [];
        foreach ($messages as $message) {
            $role = $message['role'];
            $content = $message['content'] ?? '';
            $parts[] = "[{$role}]\n{$content}";
        }

        return implode("\n\n", $parts);
    }

    /**
     * @return array<string, string>
     */
    private function entitiesForContext(): array
    {
        $entities = [];

        foreach (User::where('role', 'liderado')->where('is_active', true)->cursor() as $liderado) {
            $entities = array_merge($entities, $this->zdr->entitiesFromUser($liderado));
        }

        return $entities;
    }
}
