<?php

namespace App\Services\AI\Tools;

use App\Models\Task;
use App\Models\User;
use App\Services\AI\TeamKnowledgeService;
use App\Services\AI\TeamPerformanceService;
use Illuminate\Support\Facades\Log;

class CopilotTools
{
    private TeamPerformanceService $performance;

    private TeamKnowledgeService $knowledge;

    public function __construct(?TeamPerformanceService $performance = null, ?TeamKnowledgeService $knowledge = null)
    {
        $this->performance = $performance ?? new TeamPerformanceService;
        $this->knowledge = $knowledge ?? new TeamKnowledgeService;
    }

    /**
     * Lista de tarefas com filtros simples.
     *
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public function searchTasks(array $args): array
    {
        $status = $args['status'] ?? null;
        $priority = $args['priority'] ?? null;
        $assignedTo = $args['assigned_to'] ?? null;
        $limit = min((int) ($args['limit'] ?? 10), 20);

        $query = Task::query()->whereNotIn('status', ['cancelada']);

        if ($status && in_array($status, Task::statuses(), true)) {
            $query->where('status', $status);
        }

        if ($priority && in_array($priority, Task::priorities(), true)) {
            $query->where('priority', $priority);
        }

        if ($assignedTo) {
            $query->where('assigned_to', (int) $assignedTo);
        }

        $tasks = $query->latest()->limit($limit)->get(['id', 'title', 'status', 'priority', 'assigned_to', 'due_at']);

        return [
            'count' => $tasks->count(),
            'tasks' => $tasks->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'assigned_to' => $task->assigned_to,
                'due_at' => $task->due_at?->toIso8601String(),
                'is_overdue' => $task->isOverdue(),
            ])->all(),
        ];
    }

    /**
     * Retorna métricas e perfil resumido de um liderado.
     *
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public function getMemberProfile(array $args): array
    {
        $userId = (int) ($args['user_id'] ?? 0);
        $member = User::where('id', $userId)->where('role', 'liderado')->first();

        if (! $member) {
            return ['error' => 'Liderado não encontrado.'];
        }

        $metrics = $this->performance->memberMetrics($member);
        $profile = $member->teamProfile;

        return [
            'id' => $member->id,
            'active_tasks' => $metrics['active_tasks'],
            'overdue_tasks' => $metrics['overdue_tasks'],
            'completed_tasks' => $metrics['completed_tasks'],
            'rejected_tasks' => $metrics['rejected_tasks'],
            'avg_cycle_hours' => $metrics['avg_cycle_hours'],
            'role' => $profile?->role,
            'department' => $profile?->department,
            'function_summary' => $profile?->function_summary,
            'responsibilities' => $profile?->responsibilities ?? [],
        ];
    }

    /**
     * Busca documentos/chunks relevantes para uma query sobre um liderado.
     *
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public function searchMemberKnowledge(array $args): array
    {
        $userId = (int) ($args['user_id'] ?? 0);
        $query = $args['query'] ?? '';
        $limit = min((int) ($args['limit'] ?? 5), 10);

        $member = User::where('id', $userId)->where('role', 'liderado')->first();

        if (! $member) {
            return ['error' => 'Liderado não encontrado.'];
        }

        if (trim($query) === '') {
            return ['error' => 'Informe uma query de busca.'];
        }

        $chunks = $this->knowledge->retrieve($member, $query, $limit);

        return [
            'count' => $chunks->count(),
            'chunks' => $chunks->map(fn ($chunk) => [
                'document_id' => $chunk->document_id,
                'content' => mb_substr($chunk->content, 0, 300),
            ])->all(),
        ];
    }

    /**
     * Retorna distribuição de carga do time.
     *
     * @return array<string, mixed>
     */
    public function getWorkload(): array
    {
        $workload = $this->performance->workloadDistribution();

        return [
            'count' => $workload->count(),
            'members' => $workload->map(fn ($member) => [
                'id' => $member['member_id'],
                'active_tasks' => $member['active_tasks'],
                'overdue_tasks' => $member['overdue_tasks'],
            ])->all(),
        ];
    }

    /**
     * Gera rascunho de mensagem de cobrança para uma tarefa.
     *
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public function draftFollowUp(array $args): array
    {
        $taskId = (int) ($args['task_id'] ?? 0);
        $task = Task::find($taskId);

        if (! $task) {
            return ['error' => 'Tarefa não encontrada.'];
        }

        $assigneeName = $task->assignee?->name ?? 'liderado';
        $daysOverdue = $task->isOverdue() && $task->due_at ? now()->diffInDays($task->due_at) : 0;

        $message = "Olá, {$assigneeName}.\n\n";
        $message .= "Gostaria de verificar o andamento da tarefa \"{$task->title}\".";

        if ($daysOverdue > 0) {
            $message .= " Ela está atrasada há {$daysOverdue} dia(s).";
        }

        $message .= "\n\nPode me atualizar sobre o status e se há algum bloqueio?";

        return [
            'task_id' => $task->id,
            'title' => $task->title,
            'draft_message' => $message,
            'note' => 'Este é um rascunho. A mensagem não foi enviada automaticamente.',
        ];
    }

    /**
     * Executa uma tool pelo nome e retorna o resultado.
     *
     * @param  array<string, mixed>  $call
     * @return array<string, mixed>
     */
    public function execute(array $call): array
    {
        $name = $call['name'] ?? '';
        $args = $call['arguments'] ?? [];

        if (! is_array($args)) {
            $args = [];
        }

        if (! method_exists($this, $name)) {
            Log::warning('CopilotTools: tool desconhecida', ['name' => $name]);

            return ['error' => "Tool '{$name}' não disponível."];
        }

        try {
            $result = $this->{$name}($args);

            return ['tool' => $name, 'result' => $result];
        } catch (\Throwable $e) {
            Log::warning('CopilotTools: erro ao executar tool', ['name' => $name, 'error' => $e->getMessage()]);

            return ['tool' => $name, 'error' => 'Erro ao executar tool: '.$e->getMessage()];
        }
    }

    /**
     * Descrição das tools disponíveis para o modelo.
     *
     * @return list<array<string, mixed>>
     */
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'searchTasks',
                    'description' => 'Busca tarefas por status, prioridade ou responsável. Retorna no máximo 20 itens.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'status' => ['type' => 'string', 'enum' => Task::statuses()],
                            'priority' => ['type' => 'string', 'enum' => Task::priorities()],
                            'assigned_to' => ['type' => 'integer', 'description' => 'ID do usuário liderado'],
                            'limit' => ['type' => 'integer', 'description' => 'Máximo de resultados (máx. 20)'],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'getMemberProfile',
                    'description' => 'Retorna métricas e perfil resumido de um liderado pelo ID.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer'],
                        ],
                        'required' => ['user_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'searchMemberKnowledge',
                    'description' => 'Busca conhecimento (documentos/chunks) de um liderado para uma query.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'user_id' => ['type' => 'integer'],
                            'query' => ['type' => 'string'],
                            'limit' => ['type' => 'integer', 'description' => 'Máximo de resultados (máx. 10)'],
                        ],
                        'required' => ['user_id', 'query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'getWorkload',
                    'description' => 'Retorna distribuição de carga de tarefas ativas por liderado.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass,
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'draftFollowUp',
                    'description' => 'Gera rascunho de mensagem de cobrança para uma tarefa. Nunca envia automaticamente.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'task_id' => ['type' => 'integer'],
                        ],
                        'required' => ['task_id'],
                    ],
                ],
            ],
        ];
    }
}
