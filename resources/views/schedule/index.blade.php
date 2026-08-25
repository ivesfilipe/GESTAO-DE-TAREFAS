@extends('layouts.app')

@section('title', 'Agenda Inteligente - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">
            Agenda Inteligente
            <span class="ml-1 align-middle rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-bold uppercase text-brand-700">Beta</span>
        </h1>
        <form method="POST" action="{{ route('schedule.regenerate') }}">
            @csrf
            <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium hover:bg-slate-50">
                ↻ Gerar nova sugestão
            </button>
        </form>
    </div>

    <p class="mb-4 text-sm text-slate-500">
        As tarefas abertas são distribuídas em blocos de trabalho (seg–sex, 09h–12h e 13h–18h), priorizando críticas e atrasadas, respeitando prazos mais próximos.
    </p>

    @if($blocks->isEmpty())
        <div class="rounded-xl bg-white border border-dashed border-slate-300 p-8 text-center text-sm text-slate-400">
            Nenhuma tarefa pendente para agendar.
        </div>
    @else
        <form method="POST" action="{{ route('schedule.apply') }}">
            @csrf
            <input type="hidden" name="blocks" value="{{ json_encode($blocks->map(fn ($b) => ['task_id' => $b['task_id'], 'start' => $b['start']->format('Y-m-d H:i:s')])->all()) }}">

            <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden mb-5">
                @foreach($blocks as $block)
                    <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-50 last:border-0 {{ $block['overdue'] ? 'bg-red-50/50' : '' }}">
                        <span class="shrink-0 w-32 text-xs font-semibold {{ $block['overdue'] ? 'text-red-600' : 'text-slate-500' }}">
                            {{ $block['start']->format('d/m') }} · {{ $block['start']->format('H\hi') }}
                        </span>
                        <a href="{{ url('/tarefas/'.$block['task_id']) }}" class="min-w-0 flex-1 truncate text-sm font-medium text-slate-800 hover:text-brand-600">
                            {{ $block['title'] }}
                            @if($block['overdue'])<span class="ml-1 text-[10px] font-bold uppercase text-red-500">atrasada</span>@endif
                        </a>
                        <span class="shrink-0 text-xs text-slate-400">{{ $block['start']->diffInMinutes($block['end']) }}min · até {{ $block['end']->format('H\hi') }}</span>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit" class="rounded-lg bg-brand-700 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                    Aplicar agenda ao time
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
