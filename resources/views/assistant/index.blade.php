@extends('layouts.app')

@section('title', 'Assistente IA - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">
            Copiloto do Gestor
            <span class="ml-1 align-middle rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700">IA</span>
        </h1>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-full px-3 py-1 border {{ $status['mock'] ? 'bg-slate-100 text-slate-600 border-slate-200' : 'bg-green-50 text-green-700 border-green-200' }}">
                Provider: {{ $status['provider'] }}
            </span>
            @if($status['model'])
                <span class="rounded-full px-3 py-1 bg-slate-100 text-slate-600 border border-slate-200">
                    Modelo: {{ $status['model'] }}
                </span>
            @endif
            <span class="rounded-full px-3 py-1 border {{ $status['zdr_confirmed'] ? 'bg-green-50 text-green-700 border-green-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                ZDR: {{ $status['zdr_confirmed'] ? 'confirmado' : 'anonimizado' }}
            </span>
        </div>
    </div>

    <h2 class="text-sm font-semibold text-slate-700 mb-3">O que precisa da sua atenção hoje</h2>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-400">Atrasadas</p>
            <p class="mt-1 text-2xl font-bold text-red-600">{{ $summary['overdue'] }}</p>
        </div>
        <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-400">Bloqueadas</p>
            <p class="mt-1 text-2xl font-bold text-orange-600">{{ $summary['blocked'] }}</p>
        </div>
        <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-400">Aprovações</p>
            <p class="mt-1 text-2xl font-bold text-violet-600">{{ $summary['awaiting_approval'] }}</p>
        </div>
        <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-400">Hoje</p>
            <p class="mt-1 text-2xl font-bold text-brand-600">{{ $summary['due_today'] }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Radar prioritário</h2>
                <span class="text-xs text-slate-400">Provider: {{ $radarData['ai_provider'] }}{{ $radarData['ai_mock'] ? ' (simulação)' : '' }}</span>
            </div>
            <div class="p-5">
                <p class="text-sm text-slate-700 mb-4">{{ $radarData['summary'] }}</p>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    @foreach($radarData['metrics'] as $key => $value)
                        <div class="rounded-lg bg-slate-50 border border-slate-100 p-3 text-center">
                            <p class="text-xs text-slate-400 uppercase">{{ str_replace('_', ' ', $key) }}</p>
                            <p class="text-lg font-bold text-slate-700">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
            <h2 class="px-5 py-3.5 text-sm font-semibold text-slate-700 border-b border-slate-100">Carga por pessoa</h2>
            <div class="divide-y divide-slate-50">
                @forelse($radarData['workload'] as $member)
                    <div class="px-5 py-3 flex items-center justify-between text-sm">
                        <span class="text-slate-700 truncate">{{ $member['name'] }}</span>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center size-6 rounded-full bg-brand-50 text-brand-700 text-xs font-bold">{{ $member['active_tasks'] }}</span>
                            @if($member['overdue_tasks'] > 0)
                                <span class="inline-flex items-center justify-center size-6 rounded-full bg-red-50 text-red-600 text-xs font-bold">{{ $member['overdue_tasks'] }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-400">Nenhum liderado ativo.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2">
                <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-bold uppercase text-red-700">Ação</span>
                <h2 class="text-sm font-semibold text-slate-700">Cobranças sugeridas</h2>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($followUps as $item)
                    <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="min-w-0 flex-1">
                            <a href="/tarefas/{{ $item['task_id'] }}" class="block truncate text-sm font-medium text-slate-800 hover:text-brand-600">
                                {{ $item['title'] }}
                            </a>
                            <span class="text-xs text-slate-400">{{ $item['reason'] }}</span>
                        </div>
                        <button type="button" data-follow-up-task="{{ $item['task_id'] }}"
                                class="follow-up-btn rounded-lg bg-white border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors whitespace-nowrap">
                            Gerar rascunho
                        </button>
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-400">Nenhuma cobrança sugerida no momento.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2">
                <span class="rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-bold uppercase text-brand-700">Delegar</span>
                <h2 class="text-sm font-semibold text-slate-700">Oportunidades de delegação</h2>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($opportunities as $opportunity)
                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            @if($opportunity['type'] === 'unassigned')
                                <a href="/tarefas/{{ $opportunity['task_id'] }}" class="block truncate text-sm font-medium text-slate-800 hover:text-brand-600">
                                    {{ $opportunity['title'] }}
                                </a>
                            @else
                                <span class="block truncate text-sm font-medium text-slate-800">Liderado #{{ $opportunity['member_id'] }}</span>
                            @endif
                            <span class="text-xs text-slate-400">{{ $opportunity['reason'] }}</span>
                        </div>
                        @if($opportunity['type'] === 'unassigned')
                            <a href="/tarefas/{{ $opportunity['task_id'] }}" class="text-xs font-semibold text-brand-600 hover:text-brand-500 whitespace-nowrap">Atribuir</a>
                        @else
                            <span class="text-xs text-slate-500 whitespace-nowrap">{{ $opportunity['active_tasks'] }} ativas</span>
                        @endif
                    </div>
                @empty
                    <p class="px-5 py-4 text-sm text-slate-400">Nenhuma oportunidade identificada.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div id="follow-up-modal" class="hidden fixed inset-0 z-50 bg-slate-900/40 p-4 flex items-center justify-center">
        <div class="w-full max-w-lg rounded-xl bg-white border border-slate-200 shadow-xl overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Rascunho de cobrança</h3>
                <button type="button" data-close-modal class="text-slate-400 hover:text-slate-600">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5">
                <textarea id="follow-up-text" rows="6" readonly
                          class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-700 bg-slate-50 resize-none"></textarea>
                <p class="mt-2 text-xs text-slate-500">Este rascunho não foi enviado. Copie e envie manualmente.</p>
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-2">
            <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700">IA</span>
            <h2 class="text-sm font-semibold text-slate-700">Pergunte ao Copiloto</h2>
        </div>
        <div class="p-5">
            <div id="chat-messages" class="mb-4 max-h-80 overflow-y-auto space-y-3">
                <div class="rounded-lg bg-slate-50 border border-slate-100 p-3 text-sm text-slate-600">
                    Olá! Sou o Copiloto do Gestor. Posso ajudar com análise de risco, sugestões de delegação e visão da equipe. O que deseja saber?
                </div>
            </div>

            <div class="flex flex-wrap gap-2 mb-3">
                @foreach(['Quais tarefas estão atrasadas?', 'Quem está com mais carga?', 'Sugira cobrança para tarefas críticas', 'Resuma o radar do time'] as $example)
                    <button type="button" class="chat-example rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-600 hover:bg-slate-200 transition-colors">
                        {{ $example }}
                    </button>
                @endforeach
            </div>

            <form id="chat-form" class="flex flex-col sm:flex-row gap-2">
                @csrf
                <input type="text" id="chat-question"
                       class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
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
            div.className = `rounded-lg border p-3 text-sm ${isUser ? 'bg-brand-50 border-brand-100 text-slate-800 ml-8' : 'bg-slate-50 border-slate-100 text-slate-600 mr-8'}`;
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
