@extends('layouts.app')

@section('title', 'Assistente IA - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-5 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">
            Assistente
            <span class="ml-1 align-middle rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700">IA</span>
        </h1>
        <span class="text-xs rounded-full px-3 py-1 {{ $assistant->usesLlm() ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
            {{ $assistant->usesLlm() ? 'LLM conectado' : 'Modo heurístico (defina OPENAI_API_KEY para LLM)' }}
        </span>
    </div>

    <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-6 mb-6">
        @foreach([
            ['label' => 'Atrasadas', 'value' => $summary['overdue'], 'accent' => 'text-red-600'],
            ['label' => 'Hoje', 'value' => $summary['due_today'], 'accent' => 'text-orange-600'],
            ['label' => 'Esta semana', 'value' => $summary['due_this_week'], 'accent' => 'text-slate-900'],
            ['label' => 'Bloqueadas', 'value' => $summary['blocked'], 'accent' => 'text-red-500'],
            ['label' => 'Aguardando aprovação', 'value' => $summary['awaiting_approval'], 'accent' => 'text-violet-600'],
            ['label' => 'Concluídas na semana', 'value' => $summary['completed_this_week'], 'accent' => 'text-green-600'],
        ] as $card)
            <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm">
                <p class="text-xs font-medium text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-1 text-2xl font-bold {{ $card['accent'] }}">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl bg-gradient-to-r from-violet-50 to-brand-50 border border-violet-100 p-4 mb-6 text-sm text-slate-700">
        <strong>Resumo:</strong> {{ $summary['narrative'] }}
    </div>

    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden mb-6">
        <h2 class="px-5 py-3.5 text-sm font-semibold text-slate-700 border-b border-slate-100">Foco sugerido agora</h2>
        @forelse($suggestions as $suggestion)
            <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-50 last:border-0">
                <span class="shrink-0 inline-flex items-center justify-center size-8 rounded-lg bg-brand-50 text-brand-700 text-sm font-bold">
                    {{ round($suggestion['score']) }}
                </span>
                <a href="{{ $suggestion['url'] }}" class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800 hover:text-brand-600">
                    {{ $suggestion['title'] }}
                </a>
                <span class="hidden sm:block shrink-0 text-xs text-slate-400">{{ implode(' · ', $suggestion['reasons']) }}</span>
                <a href="{{ url('/assistente?breakdown='.$suggestion['id']) }}"
                   class="shrink-0 text-xs font-semibold text-violet-600 hover:text-violet-500">Dividir em passos</a>
            </div>
        @empty
            <p class="px-5 py-4 text-sm text-slate-400">Nenhuma tarefa aberta no momento.</p>
        @endforelse
    </div>

    @if($breakdown)
        <div class="rounded-xl bg-white border border-violet-200 shadow-sm overflow-hidden">
            <h2 class="px-5 py-3.5 text-sm font-semibold text-slate-700 border-b border-slate-100">
                Passos sugeridos para: <em>{{ $breakdown['task']->title }}</em>
            </h2>
            <ol class="divide-y divide-slate-50">
                @foreach($breakdown['steps'] as $i => $step)
                    <li class="flex items-center gap-3 px-5 py-3 text-sm text-slate-700">
                        <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">{{ $i + 1 }}</span>
                        {{ $step }}
                    </li>
                @endforeach
            </ol>
        </div>
    @endif
</div>
@endsection
