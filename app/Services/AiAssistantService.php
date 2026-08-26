<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\AI\Prompts\ManagementPrompts;
use App\Services\AI\Safety\ZeroDataRetention;
use App\Services\AI\Tools\AITools;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    private const PRIORITY_WEIGHTS = [
        'critica' => 40,
        'urgente' => 30,
        'importante' => 20,
        'normal' => 10,
    ];

    private AIService $ai;

    public function __construct(?AIService $ai = null)
    {
        $this->ai = $ai ?? app(AIService::class);
    }

    public function usesLlm(): bool
    {
        return ! $this->ai->isMock();
    }

    public function dailySummary(User $user): array
    {
        $base = $this->scopeFor($user);

        $overdue = (clone $base)->overdue()->count();
        $today = (clone $base)->whereDate('due_at', today())->whereNotIn('status', ['concluida', 'cancelada'])->count();
        $thisWeek = (clone $base)
            ->whereBetween('due_at', [today(), today()->endOfWeek()])
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->count();
        $blocked = (clone $base)->where('status', 'bloqueada')->count();
        $awaitingApproval = (clone $base)->where('status', 'aguardando_aprovacao')->when(! $user->isGestor(), fn ($q) => $q->whereRaw('1=0'))->count();
        $completedThisWeek = Task::query()
            ->whereBetween('completed_at', [today()->startOfWeek(), now()])
            ->when(! $user->isGestor(), fn ($q) => $q->where('assigned_to', $user->id))
            ->count();

        $summary = [
            'period' => now()->translatedFormat('d/m/Y'),
            'overdue' => $overdue,
            'due_today' => $today,
            'due_this_week' => $thisWeek,
            'blocked' => $blocked,
            'awaiting_approval' => $awaitingApproval,
            'completed_this_week' => $completedThisWeek,
        ];

        $summary['narrative'] = $this->buildNarrative($user, $summary);

        return $summary;
    }

    public function prioritySuggestions(User $user, int $limit = 5): array
    {
        $tasks = $this->scopeFor($user)
            ->whereIn('status', ['nova', 'recebida', 'em_andamento', 'reprovada'])
            ->with('assignee')
            ->limit(200)
            ->get()
            ->map(fn (Task $task) => [
                'task' => $task,
                'score' => $this->scoreTask($task),
                'reasons' => $this->reasonsFor($task),
            ])
            ->sortByDesc('score')
            ->take($limit)
            ->values();

        return $tasks->map(fn ($item) => [
            'id' => $item['task']->id,
            'title' => $item['task']->title,
            'score' => round($item['score'], 1),
            'reasons' => $item['reasons'],
            'url' => '/tarefas/'.$item['task']->id,
        ])->all();
    }

    public function breakdownSuggestions(Task $task): array
    {
        if ($this->ai->isMock()) {
            return $this->heuristicBreakdown($task);
        }

        try {
            $response = $this->ai->ask(
                system: ManagementPrompts::taskBreakdown(),
                user: "Tarefa: {$task->title}\nDescrição: ".($task->description ?? ''),
                temperature: 0.3,
                maxTokens: 500,
                entities: $this->entitiesForTask($task),
            );

            $steps = AITools::extractStringArray($response->content);

            if (! empty($steps)) {
                return $steps;
            }
        } catch (\Throwable $e) {
            Log::warning('LLM breakdown falhou, usando heurística', ['error' => $e->getMessage()]);
        }

        return $this->heuristicBreakdown($task);
    }

    public function generateTaskDescription(string $title, ?string $priority = null): string
    {
        if ($this->ai->isMock()) {
            return $this->heuristicTaskDescription($title, $priority);
        }

        try {
            $response = $this->ai->ask(
                system: ManagementPrompts::taskDescription(),
                user: "Tarefa: {$title}".($priority ? " (prioridade: {$priority})" : ''),
                temperature: 0.85,
                maxTokens: 400,
            );

            return trim($response->content);
        } catch (\Throwable $e) {
            Log::warning('LLM description falhou, usando heurística', ['error' => $e->getMessage()]);
        }

        return $this->heuristicTaskDescription($title, $priority);
    }

    private function heuristicTaskDescription(string $title, ?string $priority): string
    {
        $urgency = match ($priority) {
            'critica' => 'Esta é uma entrega crítica: vamos tratar como prioridade máxima do dia.',
            'urgente' => 'O relógio corre — precisamos desta entrega com urgência, sem abrir mão da qualidade.',
            'importante' => 'Importante: reserve foco dedicado para não deixarmos escorrer.',
            default => 'Prazo tranquilo, mas não deixe para depois — constância vence volume.',
        };

        $openings = [
            "Vamos colocar \"{$title}\" no papel e tirá-la do campo das ideias.",
            "\"{$title}\" tem tudo para ser uma entrega que faça diferença real.",
            "Hora de agir: \"{$title}\" está na mesa e o time conta com você.",
            "Diretor falando: \"{$title}\" é a nossa próxima cena e precisa sair impecável.",
        ];

        $opening = $openings[crc32($title) % count($openings)];

        return implode("\n", [
            $opening,
            '',
            "Objetivo: concluir \"{$title}\" com qualidade e dentro do prazo combinado.",
            'Contexto: esta tarefa conecta-se ao fluxo do setor — alguém downstream depende dela.',
            '',
            'Entregáveis:',
            '- Resultado principal concluído e revisado',
            '- Evidências/anexos registrados na tarefa',
            '- Partes interessadas comunicadas',
            '',
            $urgency,
            'Critério de sucesso: qualquer pessoa do time entende o resultado apenas lendo esta tarefa.',
        ]);
    }

    private function heuristicBreakdown(Task $task): array
    {
        $parts = preg_split('/\s+(?:e|então|depois)\s+/iu', $task->title.' '.($task->description ?? ''));

        if (count($parts) >= 2 && mb_strlen(implode('', $parts)) > 30) {
            return array_values(array_filter(array_map(
                fn ($p) => trim(mb_substr(trim((string) $p), 0, 80)),
                $parts,
            )));
        }

        return [
            'Levantar requisitos e informações necessárias para: '.$task->title,
            'Executar a atividade principal de: '.$task->title,
            'Revisar resultado e validar com as partes interessadas',
            'Registrar conclusão e documentar aprendizados',
        ];
    }

    private function scoreTask(Task $task): float
    {
        $score = self::PRIORITY_WEIGHTS[$task->priority] ?? 10;

        if (! $task->due_at) {
            return $score;
        }

        if ($task->isOverdue()) {
            $daysOver = now()->diffInDays($task->due_at);
            $score += min(50, 25 + $daysOver * 5);

            return $score;
        }

        $hoursLeft = now()->diffInHours($task->due_at, false);
        $score += match (true) {
            $hoursLeft <= 24 => 35,
            $hoursLeft <= 72 => 25,
            $hoursLeft <= 168 => 15,
            default => 5,
        };

        $ageDays = $task->created_at?->diffInDays(now()) ?? 0;
        $score += min(10, $ageDays * 0.5);

        return $score;
    }

    private function reasonsFor(Task $task): array
    {
        $reasons = [];

        if ($task->isOverdue()) {
            $reasons[] = 'Atrasada há '.$task->due_at->diffInDays(now()).' dia(s)';
        } elseif ($task->due_at && now()->diffInHours($task->due_at, false) <= 24) {
            $reasons[] = 'Vence nas próximas 24 horas';
        }

        if (in_array($task->priority, ['urgente', 'critica'])) {
            $reasons[] = 'Prioridade '.ucfirst($task->priority);
        }

        if ($task->created_at && $task->created_at->diffInDays(now()) >= 7 && in_array($task->status, ['nova', 'nao_atribuida'])) {
            $reasons[] = 'Tarefa antiga sem progresso';
        }

        return $reasons ?: ['Prioridade normal'];
    }

    private function buildNarrative(User $user, array $summary): string
    {
        if ($this->ai->isMock()) {
            return $this->heuristicNarrative($user, $summary);
        }

        try {
            $payload = [
                'overdue' => $summary['overdue'],
                'due_today' => $summary['due_today'],
                'due_this_week' => $summary['due_this_week'],
                'blocked' => $summary['blocked'],
                'awaiting_approval' => $summary['awaiting_approval'],
                'completed_this_week' => $summary['completed_this_week'],
            ];

            $response = $this->ai->ask(
                system: 'Você é um assistente de produtividade. Resuma a situação das tarefas em uma frase motivadora e prática em português.',
                user: json_encode($payload),
                temperature: 0.5,
                maxTokens: 200,
            );

            return trim($response->content);
        } catch (\Throwable $e) {
            Log::warning('LLM narrative falhou', ['error' => $e->getMessage()]);
        }

        return $this->heuristicNarrative($user, $summary);
    }

    private function heuristicNarrative(User $user, array $summary): string
    {
        $parts = [];

        if ($summary['overdue'] > 0) {
            $parts[] = "Atenção: {$summary['overdue']} tarefa(s) atrasada(s).";
        }
        if ($summary['due_today'] > 0) {
            $parts[] = "Você tem {$summary['due_today']} entrega(s) hoje.";
        }
        if ($summary['awaiting_approval'] > 0 && $user->isGestor()) {
            $parts[] = "{$summary['awaiting_approval']} tarefa(s) aguardando sua aprovação.";
        }
        if ($summary['blocked'] > 0) {
            $parts[] = "{$summary['blocked']} tarefa(s) bloqueadas precisam de atenção.";
        }
        if ($parts === []) {
            $parts[] = 'Nada crítico no radar. Bom momento para adiantar o backlog!';
        }

        return implode(' ', $parts);
    }

    private function scopeFor(User $user)
    {
        return Task::query()
            ->whereNotIn('status', ['cancelada'])
            ->when(! $user->isGestor(), fn ($q) => $q->where('assigned_to', $user->id));
    }

    private function entitiesForTask(Task $task): array
    {
        return app(ZeroDataRetention::class)->entitiesFromTask($task);
    }
}
