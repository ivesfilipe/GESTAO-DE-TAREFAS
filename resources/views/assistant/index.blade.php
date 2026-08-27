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

    <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center gap-2">
            <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700">IA</span>
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Pergunte ao Copiloto</h2>
        </div>
        <div class="p-5">
            <div id="chat-messages" class="mb-4 max-h-80 overflow-y-auto space-y-3">
                <div class="rounded-lg bg-slate-50 dark:bg-white/[0.04] border border-slate-100 dark:border-white/5 p-3 text-sm text-slate-600 dark:text-slate-400">
                    Olá! Sou o Copiloto do Gestor. Posso ajudar com análise de risco, sugestões de delegação e visão da equipe. O que deseja saber?
                </div>
            </div>

            <div class="flex flex-wrap gap-2 mb-3">
                @foreach(['Quais tarefas estão atrasadas?', 'Quem está com mais carga?', 'Sugira cobrança para tarefas críticas', 'Resuma o radar do time'] as $example)
                    <button type="button" class="chat-example rounded-full bg-slate-100 dark:bg-white/[0.06] px-3 py-1 text-xs text-slate-600 dark:text-slate-400 hover:bg-slate-200 transition-colors">
                        {{ $example }}
                    </button>
                @endforeach
            </div>

            <form id="chat-form" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <input type="text" id="chat-question"
                       class="flex-1 rounded-lg border border-slate-300 dark:border-white/10 px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                       placeholder="Ex.: qual o maior risco do time hoje?" maxlength="1000"/>
                <button type="submit" id="chat-submit"
                        class="rounded-lg bg-brand-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors whitespace-nowrap">
                    Perguntar
                </button>
            </form>
            <p id="chat-error" class="hidden mt-2 text-xs text-red-700"></p>
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

        function appendMessage(text, isUser) {
            const div = document.createElement('div');
            div.className = `rounded-lg border p-3 text-sm ${isUser ? 'bg-brand-50 border-brand-100 text-slate-800 dark:text-slate-100 ml-8' : 'bg-slate-50 dark:bg-white/[0.04] border-slate-100 dark:border-white/5 text-slate-600 dark:text-slate-400 mr-8'}`;
            div.textContent = text;
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const question = input.value.trim();
            if (!question) return;

            appendMessage(question, true);
            input.value = '';
            submitBtn.disabled = true;
            submitBtn.textContent = '...';
            errorBox.classList.add('hidden');

            window.axios.post('/assistente/perguntar', { question })
                .then(({ data }) => {
                    appendMessage(data.answer, false);
                })
                .catch((err) => {
                    errorBox.textContent = err.response?.data?.message ?? 'Não foi possível obter resposta.';
                    errorBox.classList.remove('hidden');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Perguntar';
                });
        });

        document.querySelectorAll('.chat-example').forEach(btn => {
            btn.addEventListener('click', () => {
                input.value = btn.textContent.trim();
                form.dispatchEvent(new Event('submit'));
            });
        });
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
