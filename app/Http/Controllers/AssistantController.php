<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Services\AI\AIService;
use App\Services\AI\CopilotService;
use App\Services\AI\ManagementRadarService;
use App\Services\AiAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssistantController extends Controller
{
    public function index(Request $request, AiAssistantService $assistant, ManagementRadarService $radar)
    {
        Gate::authorize('create-task');

        $summary = $assistant->dailySummary($request->user());
        $suggestions = $assistant->prioritySuggestions($request->user());
        $radarData = $radar->radar($request->user());

        $status = $this->aiStatus();
        $followUps = $this->followUpSuggestions();
        $opportunities = $this->delegationOpportunities();

        $breakdown = null;
        if ($request->filled('breakdown')) {
            $task = Task::findOrFail($request->input('breakdown'));
            Gate::authorize('view-task', $task);
            $breakdown = ['task' => $task, 'steps' => $assistant->breakdownSuggestions($task)];
        }

        return view('assistant.index', compact('assistant', 'summary', 'suggestions', 'breakdown', 'radarData', 'status', 'followUps', 'opportunities'));
    }

    public function ask(Request $request, CopilotService $copilot)
    {
        Gate::authorize('create-task');

        $data = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $result = $copilot->answer($request->user(), $data['question']);

            return response()->json([
                'ok' => true,
                'answer' => $result['answer'],
                'provider' => $result['provider'],
                'mock' => $result['mock'],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'answer' => 'IA temporariamente indisponível. Tente novamente em instantes.',
                'provider' => app(AIService::class)->provider()->name(),
                'mock' => app(AIService::class)->isMock(),
            ], 200);
        }
    }

    public function suggestCollection(Request $request, CopilotService $copilot)
    {
        Gate::authorize('create-task');

        $data = $request->validate([
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
        ]);

        $task = Task::findOrFail($data['task_id']);
        Gate::authorize('view-task', $task);

        $result = $copilot->suggestCollection($request->user(), $task);

        return response()->json([
            'ok' => true,
            'draft' => $result['draft'],
            'provider' => $result['provider'],
            'mock' => $result['mock'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function aiStatus(): array
    {
        $ai = app(AIService::class);
        $provider = $ai->provider()->name();
        $model = config("ai.providers.{$provider}.model") ?? '—';
        $configured = ! $ai->isMock();
        $zdrConfirmed = config('ai.zdr.confirmed', false) || ! config('ai.zdr.required', true);

        return [
            'provider' => $provider,
            'model' => $model,
            'configured' => $configured,
            'mock' => ! $configured,
            'zdr_confirmed' => $zdrConfirmed,
            'operational' => $configured,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function followUpSuggestions(int $limit = 5): array
    {
        return Task::query()
            ->whereNotIn('status', ['concluida', 'cancelada'])
            ->where(function ($q) {
                $q->overdue()
                    ->orWhere(function ($q2) {
                        $q2->whereDate('due_at', today())
                            ->whereNotIn('status', ['concluida', 'cancelada']);
                    })
                    ->orWhere('status', 'bloqueada');
            })
            ->whereIn('priority', ['urgente', 'critica'])
            ->orderBy('due_at')
            ->limit($limit)
            ->get()
            ->map(fn (Task $task) => [
                'task_id' => $task->id,
                'title' => $task->title,
                'reason' => $task->isOverdue() ? 'Atrasada' : ($task->status === 'bloqueada' ? 'Bloqueada' : 'Vence hoje'),
            ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function delegationOpportunities(int $limit = 5): array
    {
        $unassigned = Task::where('status', 'nao_atribuida')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn (Task $task) => [
                'type' => 'unassigned',
                'task_id' => $task->id,
                'title' => $task->title,
                'reason' => 'Sem responsável',
            ]);

        $lowLoad = User::where('role', 'liderado')
            ->where('is_active', true)
            ->get()
            ->filter(fn (User $u) => $u->assignedTasks()->whereNotIn('status', ['concluida', 'cancelada'])->count() === 0)
            ->take($limit)
            ->map(fn (User $u) => [
                'type' => 'member',
                'member_id' => $u->id,
                'title' => $u->name,
                'reason' => 'Sem tarefas ativas',
                'active_tasks' => 0,
            ]);

        return $unassigned->merge($lowLoad)->values()->take($limit)->all();
    }
}
