@extends('layouts.app')

@section('title', 'Assistente IA - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
            Copiloto do Gestor
            <span class="ml-1 align-middle rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700">IA</span>
        </h1>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-full px-3 py-1 border {{ $status['mock'] ? 'bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-400 border-slate-200 dark:border-white/10' : 'bg-green-50 text-green-700 border-green-200' }}">
                Provider: {{ $status['provider'] }}
            </span>
            @if($status['model'])
                <span class="rounded-full px-3 py-1 bg-slate-100 dark:bg-white/[0.06] text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-white/10">
                    Modelo: {{ $status['model'] }}
                </span>
            @endif
            <span class="rounded-full px-3 py-1 border {{ $status['zdr_confirmed'] ? 'bg-green-50 text-green-700 border-green-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                ZDR: {{ $status['zdr_confirmed'] ? 'confirmado' : 'anonimizado' }}
            </span>
        </div>
    </div>

    <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">O que precisa da sua atenção hoje</h2>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="group relative overflow-hidden rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 p-4 shadow-sm hover:-translate-y-0.5 hover:shadow-md dark:hover:border-red-500/20 transition-all">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-red-50/60 dark:from-red-500/[0.07] via-transparent to-transparent opacity-70 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-6 -right-6 size-16 rounded-full bg-red-100/50 dark:bg-red-500/15 blur-xl"></div>
            <p class="relative text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">Atrasadas</p>
            <p class="relative mt-1 text-2xl font-bold tracking-tight text-red-600 dark:text-red-400">{{ $summary['overdue'] }}</p>
        </div>
        <div class="group relative overflow-hidden rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 p-4 shadow-sm hover:-translate-y-0.5 hover:shadow-md dark:hover:border-orange-500/20 transition-all">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-orange-50/60 dark:from-orange-500/[0.07] via-transparent to-transparent opacity-70 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-6 -right-6 size-16 rounded-full bg-orange-100/50 dark:bg-orange-500/15 blur-xl"></div>
            <p class="relative text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">Bloqueadas</p>
            <p class="relative mt-1 text-2xl font-bold tracking-tight text-orange-600 dark:text-orange-400">{{ $summary['blocked'] }}</p>
        </div>
        <div class="group relative overflow-hidden rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 p-4 shadow-sm hover:-translate-y-0.5 hover:shadow-md dark:hover:border-violet-500/20 transition-all">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-violet-50/60 dark:from-violet-500/[0.07] via-transparent to-transparent opacity-70 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-6 -right-6 size-16 rounded-full bg-violet-100/50 dark:bg-violet-500/15 blur-xl"></div>
            <p class="relative text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">Aprovações</p>
            <p class="relative mt-1 text-2xl font-bold tracking-tight text-violet-600 dark:text-violet-400">{{ $summary['awaiting_approval'] }}</p>
        </div>
        <div class="group relative overflow-hidden rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 p-4 shadow-sm hover:-translate-y-0.5 hover:shadow-md dark:hover:border-brand-500/20 transition-all">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-brand-50/60 dark:from-brand-500/[0.07] via-transparent to-transparent opacity-70 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-6 -right-6 size-16 rounded-full bg-brand-100/50 dark:bg-brand-500/15 blur-xl"></div>
            <p class="relative text-xs font-semibold tracking-wide text-slate-400 dark:text-slate-500">Hoje</p>
            <p class="relative mt-1 text-2xl font-bold tracking-tight text-brand-600 dark:text-brand-400">{{ $summary['due_today'] }}</p>
        </div>
    </div>

    <details class="mb-6 rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm">
        <summary class="cursor-pointer px-5 py-3.5 text-sm font-semibold text-slate-700 dark:text-slate-300">Radar, cobranças e delegação</summary>
        <div class="px-5 pb-5">
    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Radar prioritário</h2>
                <span class="text-xs text-slate-400 dark:text-slate-500">Provider: {{ $radarData['ai_provider'] }}{{ $radarData['ai_mock'] ? ' (simulação)' : '' }}</span>
            </div>
            <div class="p-5">
                <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">{{ $radarData['summary'] }}</p>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    @foreach($radarData['metrics'] as $key => $value)
                        <div class="rounded-lg bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/5 p-3 text-center">
                            <p class="text-xs text-slate-400 dark:text-slate-500 uppercase">{{ str_replace('_', ' ', $key) }}</p>
                            <p class="text-lg font-bold text-slate-700 dark:text-slate-300">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden">
            <h2 class="px-5 py-3.5 text-sm font-semibold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5">Carga por pessoa</h2>
            <div class="divide-y divide-slate-50 dark:divide-white/5">
                @forelse($radarData['workload'] as $member)
                    <div class="px-5 py-3 flex items-center justify-between text-sm">
                        <span class="text-slate-700 dark:text-slate-300 truncate">{{ $member['name'] }}</span>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center size-6 rounded-full bg-brand-50 text-brand-700 text-xs font-bold">{{ $member['active_tasks'] }}</span>
                            @if($member['overdue_tasks'] > 0)
                                <span class="inline-flex items-center justify-center size-6 rounded-full bg-red-50 text-red-600 text-xs font-bold">{{ $member['overdue_tasks'] }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-400 dark:text-slate-500">Nenhum liderado ativo.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center gap-2">
                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase text-red-700">Ação</span>
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Cobranças sugeridas</h2>
            </div>
            <div class="divide-y divide-slate-50 dark:divide-white/5">
                @forelse($followUps as $item)
                    <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <a href="/tarefas/{{ $item['task_id'] }}" class="block truncate text-sm font-medium text-slate-800 dark:text-slate-100 hover:text-brand-600">
                                {{ $item['title'] }}
                            </a>
                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $item['reason'] }}</span>
                        </div>
                        <button type="button" data-follow-up-task="{{ $item['task_id'] }}"
                                class="follow-up-btn rounded-lg bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-300 dark:border-white/10 px-3 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors whitespace-nowrap">
                            Gerar rascunho
                        </button>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-400 dark:text-slate-500">Nenhuma cobrança sugerida no momento.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center gap-2">
                <span class="rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-bold uppercase text-brand-700">Delegar</span>
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Oportunidades de delegação</h2>
            </div>
            <div class="divide-y divide-slate-50 dark:divide-white/5">
                @forelse($opportunities as $opportunity)
                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            @if($opportunity['type'] === 'unassigned')
                                <a href="/tarefas/{{ $opportunity['task_id'] }}" class="block truncate text-sm font-medium text-slate-800 dark:text-slate-100 hover:text-brand-600">
                                    {{ $opportunity['title'] }}
                                </a>
                            @else
                                <span class="block truncate text-sm font-medium text-slate-800 dark:text-slate-100">Liderado #{{ $opportunity['member_id'] }}</span>
                            @endif
                            <span class="text-xs text-slate-400 dark:text-slate-500">{{ $opportunity['reason'] }}</span>
                        </div>
                        @if($opportunity['type'] === 'unassigned')
                            <a href="/tarefas/{{ $opportunity['task_id'] }}" class="text-xs font-semibold text-brand-600 hover:text-brand-500 whitespace-nowrap">Atribuir</a>
                        @else
                            <span class="text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">{{ $opportunity['active_tasks'] }} ativas</span>
                        @endif
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-400 dark:text-slate-500">Nenhuma oportunidade identificada.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div id="follow-up-modal" class="hidden fixed inset-0 z-50 bg-slate-900/40 p-4 flex items-center justify-center">
        <div class="w-full max-w-lg rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Rascunho de cobrança</h3>
                <button type="button" data-close-modal class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:text-slate-400">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5">
                <textarea id="follow-up-text" rows="6" readonly
                          class="w-full rounded-lg border border-slate-300 dark:border-white/10 px-4 py-2.5 text-sm text-slate-700 bg-slate-50 dark:bg-white/[0.04] resize-none"></textarea>
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Este rascunho não foi enviado. Copie e envie manualmente.</p>
            </div>
        </div>
    </div>

        </div>
    </details>

    <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Top 5 ações prioritárias</h2>
            <span class="text-xs text-slate-400 dark:text-slate-500">Determinístico (não usa IA)</span>
        </div>
        <div class="divide-y divide-slate-50 dark:divide-white/5">
            @forelse($topPriorities as $index => $item)
                <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <a href="/tarefas/{{ $item['task_id'] }}" class="block truncate text-sm font-medium text-slate-800 dark:text-slate-100 hover:text-brand-600">
                            {{ $item['title'] }}
                        </a>
                        <span class="text-xs text-slate-400 dark:text-slate-500">{{ implode(' · ', $item['reasons']) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center size-6 rounded-full bg-brand-50 text-brand-700 text-xs font-bold">{{ $index + 1 }}</span>
                        <span class="inline-flex items-center justify-center size-6 rounded-full
                            @if($item['priority'] === 'critica') bg-red-50 text-red-600
                            @elseif($item['priority'] === 'urgente') bg-orange-50 text-orange-600
                            @elseif($item['priority'] === 'importante') bg-amber-50 text-amber-600
                            @else bg-slate-50 text-slate-600
                            @endif text-xs font-bold">
                            {{ $item['priority'] }}
                        </span>
                    </div>
                </div>
            @empty
                <p class="px-5 py-4 text-sm text-slate-400 dark:text-slate-500">Nenhuma ação prioritária identificada.</p>
            @endforelse
        </div>
    </div>

    <div class="grid lg:grid-cols-5 gap-4 mb-6">
        <div class="lg:col-span-3 rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden flex flex-col min-h-[28rem]">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center gap-2">
                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700">IA</span>
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Pergunte ao Copiloto</h2>
            </div>
            <div id="chat-messages" class="flex-1 overflow-y-auto space-y-3 p-5">
                <div class="rounded-lg bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/5 p-3 text-sm text-slate-600 dark:text-slate-400">
                    Olá! Sou o Copiloto do Gestor. Pergunte sobre tarefas, anexe PDF, Word, Excel ou imagem, e clique nos cards para abrir a tarefa ao lado.
                </div>
            </div>
            <div class="p-4 border-t border-slate-100 dark:border-white/5">
                <div id="chat-attachments" class="hidden mb-2 text-xs text-slate-500 dark:text-slate-400"></div>
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach(['Quais tarefas estão atrasadas?', 'Quem está com mais carga?', 'Resuma o radar do time'] as $example)
                        <button type="button" class="chat-example rounded-full bg-slate-100 dark:bg-white/[0.06] px-3 py-1 text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-200 transition-colors">
                            {{ $example }}
                        </button>
                    @endforeach
                </div>
                <form id="chat-form" class="flex gap-2 items-end">
                    @csrf
                    <label class="shrink-0 cursor-pointer rounded-lg border border-slate-300 dark:border-white/10 p-2.5 text-slate-500 hover:bg-slate-50 dark:hover:bg-white/[0.04]" title="Anexar arquivo">
                        <input type="file" id="chat-file" class="hidden" accept=".pdf,.docx,.xlsx,.csv,.txt,.md,.jpg,.jpeg,.png,.webp"/>
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    </label>
                    <input type="text" id="chat-question"
                           class="flex-1 min-w-0 rounded-lg border border-slate-300 dark:border-white/10 px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                           placeholder="Pergunte ou anexe um arquivo..." maxlength="2000"/>
                    <button type="submit" id="chat-submit"
                            class="rounded-lg bg-brand-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors whitespace-nowrap">
                        Enviar
                    </button>
                </form>
                <p id="chat-error" class="hidden mt-2 text-xs text-red-700"></p>
            </div>
        </div>

        <div id="task-panel" class="lg:col-span-2 rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden flex flex-col min-h-[16rem]">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Tarefa</h2>
                <button type="button" id="task-panel-close" class="lg:hidden text-slate-400">Fechar</button>
            </div>
            <div id="task-panel-body" class="p-5 text-sm text-slate-500 dark:text-slate-400">
                Clique em uma tarefa da conversa para ver os detalhes aqui.
            </div>
        </div>
    </div>

    @if($breakdown)
        <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-violet-200 shadow-sm overflow-hidden">
            <h2 class="px-5 py-3.5 text-sm font-semibold text-slate-700 dark:text-slate-300 border-b border-slate-100 dark:border-white/5">
                Passos sugeridos para: <em>{{ $breakdown['task']->title }}</em>
            </h2>
            <ol class="divide-y divide-slate-50 dark:divide-white/5">
                @foreach($breakdown['steps'] as $i => $step)
                    <li class="flex items-center gap-3 px-5 py-3 text-sm text-slate-700 dark:text-slate-300">
                        <span class="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700">{{ $i + 1 }}</span>
                        {{ $step }}
                    </li>
                @endforeach
            </ol>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('chat-form');
        const input = document.getElementById('chat-question');
        const messages = document.getElementById('chat-messages');
        const errorBox = document.getElementById('chat-error');
        const submitBtn = document.getElementById('chat-submit');
        const fileInput = document.getElementById('chat-file');
        const attachBox = document.getElementById('chat-attachments');
        const panelBody = document.getElementById('task-panel-body');
        const documentIds = [];

        function appendMessage(text, isUser) {
            const div = document.createElement('div');
            div.className = `rounded-lg border p-3 text-sm whitespace-pre-wrap ${isUser ? 'bg-brand-50 border-brand-100 text-slate-800 dark:text-slate-100 ml-8' : 'bg-slate-50 dark:bg-white/[0.04] border-slate-100 dark:border-white/5 text-slate-600 dark:text-slate-400 mr-8'}`;
            div.textContent = text;
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
            return div;
        }

        function appendTaskCards(tasks) {
            if (!tasks || !tasks.length) return;
            const wrap = document.createElement('div');
            wrap.className = 'space-y-2 mr-8';
            tasks.forEach((task) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left rounded-lg border border-slate-200 dark:border-white/10 p-3 hover:border-brand-400 transition-colors';
                const title = document.createElement('p');
                title.className = 'text-sm font-semibold text-slate-800 dark:text-slate-100';
                title.textContent = task.title;
                const meta = document.createElement('p');
                meta.className = 'text-xs text-slate-500 dark:text-slate-400 mt-1';
                meta.textContent = [task.status, task.assignee, task.due_at].filter(Boolean).join(' · ');
                btn.appendChild(title);
                btn.appendChild(meta);
                btn.addEventListener('click', () => openTask(task.id));

                // Botão "Dividir em passos"
                const breakdownBtn = document.createElement('button');
                breakdownBtn.type = 'button';
                breakdownBtn.className = 'mt-2 text-xs font-semibold text-violet-600 hover:text-violet-500';
                breakdownBtn.textContent = '✨ Dividir em passos';
                breakdownBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    requestBreakdown(task.id);
                });
                btn.appendChild(breakdownBtn);

                wrap.appendChild(btn);
            });
            messages.appendChild(wrap);
            messages.scrollTop = messages.scrollHeight;
        }

        function openTask(id) {
            window.axios.get(`/tarefas/${id}/resumo`)
                .then(({ data }) => {
                    panelBody.replaceChildren();
                    const title = document.createElement('p');
                    title.className = 'text-base font-semibold text-slate-900 dark:text-white';
                    title.textContent = data.title;
                    const meta = document.createElement('p');
                    meta.className = 'mt-2 text-xs text-slate-500 dark:text-slate-400';
                    meta.textContent = [data.status, data.priority, data.assignee].filter(Boolean).join(' · ');
                    panelBody.appendChild(title);
                    panelBody.appendChild(meta);
                    if (data.due_at) {
                        const due = document.createElement('p');
                        due.className = 'mt-1 text-xs text-slate-500';
                        due.textContent = 'Prazo: ' + data.due_at;
                        panelBody.appendChild(due);
                    }
                    if (data.description) {
                        const desc = document.createElement('p');
                        desc.className = 'mt-3 text-sm text-slate-600 dark:text-slate-300 whitespace-pre-wrap';
                        desc.textContent = data.description;
                        panelBody.appendChild(desc);
                    }
                    const link = document.createElement('a');
                    link.href = data.url;
                    link.className = 'inline-flex mt-4 rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white';
                    link.textContent = 'Abrir tarefa';
                    panelBody.appendChild(link);
                })
                .catch(() => {
                    panelBody.textContent = 'Não foi possível carregar a tarefa.';
                });
        }

        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;
            const body = new FormData();
            body.append('file', file);
            attachBox.classList.remove('hidden');
            attachBox.textContent = 'Enviando ' + file.name + '...';
            window.axios.post('/assistente/anexos', body)
                .then(({ data }) => {
                    if (data.document_id) documentIds.push(data.document_id);
                    attachBox.textContent = data.filename + ' (' + data.status + ')';
                    appendMessage('Arquivo anexado à base da empresa: ' + data.filename, true);
                })
                .catch((err) => {
                    attachBox.textContent = err.response?.data?.message ?? 'Falha ao anexar.';
                })
                .finally(() => { fileInput.value = ''; });
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const question = input.value.trim();
            if (!question) return;

            appendMessage(question, true);
            input.value = '';
            submitBtn.disabled = true;
            submitBtn.textContent = '...';
            errorBox.classList.add('hidden');

            window.axios.post('/assistente/perguntar', { question, document_ids: documentIds })
                .then(({ data }) => {
                    appendMessage(data.answer, false);
                    appendTaskCards(data.tasks || []);
                    if (data.open_task_id) openTask(data.open_task_id);
                })
                .catch((err) => {
                    errorBox.textContent = err.response?.data?.message ?? 'Não foi possível obter resposta.';
                    errorBox.classList.remove('hidden');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Enviar';
                });
        });

        document.querySelectorAll('.chat-example').forEach(btn => {
            btn.addEventListener('click', () => {
                input.value = btn.textContent.trim();
                form.dispatchEvent(new Event('submit'));
            });
        });

        document.getElementById('task-panel-close').addEventListener('click', () => {
            panelBody.textContent = 'Clique em uma tarefa da conversa para ver os detalhes aqui.';
        });

        function requestBreakdown(taskId) {
            window.axios.post('{{ route('assistant.breakdown') }}', { task_id: taskId })
                .then(({ data }) => {
                    if (data.ok && data.steps) {
                        panelBody.replaceChildren();
                        const title = document.createElement('p');
                        title.className = 'text-base font-semibold text-slate-900 dark:text-white mb-2';
                        title.textContent = 'Passos sugeridos';
                        panelBody.appendChild(title);
                        const ol = document.createElement('ol');
                        ol.className = 'space-y-2';
                        data.steps.forEach((step, i) => {
                            const li = document.createElement('li');
                            li.className = 'flex items-start gap-2 text-sm text-slate-700 dark:text-slate-300';
                            const num = document.createElement('span');
                            num.className = 'inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-violet-100 text-xs font-bold text-violet-700';
                            num.textContent = i + 1;
                            li.appendChild(num);
                            li.appendChild(document.createTextNode(step));
                            ol.appendChild(li);
                        });
                        panelBody.appendChild(ol);
                    }
                })
                .catch(() => {
                    panelBody.textContent = 'Não foi possível gerar os passos.';
                });
        }
    })();

    (function () {
        const modal = document.getElementById('follow-up-modal');
        const textArea = document.getElementById('follow-up-text');

        document.querySelectorAll('.follow-up-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const taskId = btn.dataset.followUpTask;
                btn.disabled = true;
                btn.textContent = '...';

                window.axios.post('{{ route('assistant.collection') }}', { task_id: taskId })
                    .then(({ data }) => {
                        if (data.ok && data.draft) {
                            textArea.value = data.draft;
                            modal.classList.remove('hidden');
                        }
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.textContent = 'Gerar rascunho';
                    });
            });
        });

        modal.querySelector('[data-close-modal]').addEventListener('click', () => {
            modal.classList.add('hidden');
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.classList.add('hidden');
        });
    })();
</script>
@endpush
