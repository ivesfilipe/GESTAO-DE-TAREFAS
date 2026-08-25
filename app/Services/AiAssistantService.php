<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    private const PRIORITY_WEIGHTS = [
        'critica' => 40,
        'urgente' => 30,
        'importante' => 20,
        'normal' => 10,
    ];

    private ?string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('services.openai.key') ?? env('OPENAI_API_KEY') ?? '';
    }

    public function usesLlm(): bool
    {
        return ! empty($this->apiKey);
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
        if ($this->usesLlm()) {
            try {
                return $this->llmBreakdown($task);
            } catch (\Throwable $e) {
                Log::warning('LLM breakdown falhou, usando heurística', ['error' => $e->getMessage()]);
            }
        }

        return $this->heuristicBreakdown($task);
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

    private function llmBreakdown(Task $task): array
    {
        $response = Http::withToken($this->apiKey)
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => 'Você divide tarefas em subtarefas objetivas. Responda apenas com um array JSON de strings em português.'],
                    ['role' => 'user', 'content' => "Tarefa: {$task->title}\nDescrição: {$task->description}"],
                ],
                'temperature' => 0.3,
            ]);

        $response->throw();

        $content = $response->json('choices.0.message.content');

        return collect(json_decode($content, true))
            ->filter(fn ($v) => is_string($v))
            ->values()
            ->all();
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
        if ($this->usesLlm()) {
            try {
                return $this->llmNarrative($user, $summary);
            } catch (\Throwable $e) {
                Log::warning('LLM narrative falhou', ['error' => $e->getMessage()]);
            }
        }

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

    private function llmNarrative(User $user, array $summary): string
    {
        unset($summary['narrative']);

        $response = Http::withToken($this->apiKey)
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => 'Você é um assistente de produtividade. Resuma a situação das tarefas em uma frase motivadora e prática em português.'],
                    ['role' => 'user', 'content' => json_encode($summary)],
                ],
                'temperature' => 0.5,
            ]);

        $response->throw();

        return trim($response->json('choices.0.message.content'));
    }

    private function scopeFor(User $user)
    {
        return Task::query()
            ->whereNotIn('status', ['cancelada'])
            ->when(! $user->isGestor(), fn ($q) => $q->where('assigned_to', $user->id));
    }
}
