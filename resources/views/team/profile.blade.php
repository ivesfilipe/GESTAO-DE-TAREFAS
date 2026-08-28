@extends('layouts.app')

@section('title', 'Perfil de '.$user->name.' - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $user->name }}</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Perfil inteligente do liderado</p>
        </div>
        <a href="{{ route('team.index') }}" class="rounded-lg border border-slate-300 dark:border-white/10 px-4 py-2.5 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors text-center">
            Voltar para equipe
        </a>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Indicadores operacionais</h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Tarefas abertas</span>
                    <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $metrics['active_tasks'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Atrasadas</span>
                    <span class="text-sm font-bold text-red-600">{{ $metrics['overdue_tasks'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Concluídas</span>
                    <span class="text-sm font-bold text-green-600">{{ $metrics['completed_tasks'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Reprovadas</span>
                    <span class="text-sm font-bold text-orange-600">{{ $metrics['rejected_tasks'] }} ({{ $metrics['rejection_rate'] }}%)</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-500 dark:text-slate-400">Ciclo médio</span>
                    <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $metrics['avg_cycle_hours'] ? $metrics['avg_cycle_hours'].'h' : 'n/a' }}</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Perfil profissional</h2>
                <button type="button" id="edit-profile-toggle" class="text-xs font-semibold text-brand-600 hover:text-brand-500">
                    Editar
                </button>
            </div>

            <div id="profile-read" class="p-5 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-[11px] uppercase font-semibold text-slate-400 dark:text-slate-500">Função</p>
                        <p class="text-sm text-slate-800 dark:text-slate-100">{{ $profile?->role ?: '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase font-semibold text-slate-400 dark:text-slate-500">Setor</p>
                        <p class="text-sm text-slate-800 dark:text-slate-100">{{ $profile?->department ?: '—' }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-[11px] uppercase font-semibold text-slate-400 dark:text-slate-500">Resumo da função</p>
                    <p class="text-sm text-slate-800 dark:text-slate-100">{{ $profile?->function_summary ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase font-semibold text-slate-400 dark:text-slate-500">Responsabilidades</p>
                    @if(!empty($profile?->responsibilities))
                        <ul class="list-disc list-inside text-sm text-slate-700 dark:text-slate-300 space-y-1">
                            @foreach($profile->responsibilities as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">—</p>
                    @endif
                </div>
                <div>
                    <p class="text-[11px] uppercase font-semibold text-slate-400 dark:text-slate-500">Responsabilidades recorrentes</p>
                    @if(!empty($profile?->recurring_responsibilities))
                        <ul class="list-disc list-inside text-sm text-slate-700 dark:text-slate-300 space-y-1">
                            @foreach($profile->recurring_responsibilities as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">—</p>
                    @endif
                </div>
                <div>
                    <p class="text-[11px] uppercase font-semibold text-slate-400 dark:text-slate-500">Objetivos profissionais</p>
                    @if(!empty($profile?->professional_objectives))
                        <ul class="list-disc list-inside text-sm text-slate-700 dark:text-slate-300 space-y-1">
                            @foreach($profile->professional_objectives as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">—</p>
                    @endif
                </div>
                <div>
                    <p class="text-[11px] uppercase font-semibold text-slate-400 dark:text-slate-500">Orientações de delegação</p>
                    <p class="text-sm text-slate-800 dark:text-slate-100">{{ $profile?->delegation_guidelines ?: '—' }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('team.profile.update', $user) }}" id="profile-form" class="hidden p-5 space-y-4">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Função</label>
                        <input type="text" name="role" value="{{ old('role', $profile?->role) }}" maxlength="255"
                               class="w-full rounded-lg border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"/>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Setor</label>
                        <input type="text" name="department" value="{{ old('department', $profile?->department) }}" maxlength="255"
                               class="w-full rounded-lg border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"/>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Resumo da função</label>
                    <textarea name="function_summary" rows="3"
                              class="w-full rounded-lg border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">{{ old('function_summary', $profile?->function_summary) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Responsabilidades (uma por linha)</label>
                    <textarea name="responsibilities" rows="3"
                              class="w-full rounded-lg border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">{{ old('responsibilities', implode("\n", $profile?->responsibilities ?? [])) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Responsabilidades recorrentes (uma por linha)</label>
                    <textarea name="recurring_responsibilities" rows="3"
                              class="w-full rounded-lg border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">{{ old('recurring_responsibilities', implode("\n", $profile?->recurring_responsibilities ?? [])) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Objetivos profissionais (um por linha)</label>
                    <textarea name="professional_objectives" rows="3"
                              class="w-full rounded-lg border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">{{ old('professional_objectives', implode("\n", $profile?->professional_objectives ?? [])) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Orientações de delegação</label>
                    <textarea name="delegation_guidelines" rows="3"
                              class="w-full rounded-lg border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none">{{ old('delegation_guidelines', $profile?->delegation_guidelines) }}</textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" id="cancel-profile-edit" class="rounded-lg border border-slate-300 dark:border-white/10 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-white/[0.04] transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                        Salvar perfil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Análise do perfil</h2>
                <button type="button" id="generate-profile"
                        class="inline-flex items-center gap-1 rounded-full bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-500 transition-colors">
                    ✨ Atualizar inteligência
                </button>
            </div>
            <div id="profile-content" class="p-5">
                @if($profile?->summary)
                    <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">{{ $profile->summary }}</p>
                    @if(!empty($profile->strengths))
                        <div class="mb-3">
                            <p class="text-xs uppercase font-semibold text-slate-400 dark:text-slate-500 mb-1">Pontos fortes</p>
                            <ul class="list-disc list-inside text-sm text-slate-700 dark:text-slate-300 space-y-1">
                                @foreach($profile->strengths as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(!empty($profile->gaps))
                        <div class="mb-3">
                            <p class="text-xs uppercase font-semibold text-slate-400 dark:text-slate-500 mb-1">Oportunidades</p>
                            <ul class="list-disc list-inside text-sm text-slate-700 dark:text-slate-300 space-y-1">
                                @foreach($profile->gaps as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(!empty($profile->preferences))
                        <div>
                            <p class="text-xs uppercase font-semibold text-slate-400 dark:text-slate-500 mb-1">Preferências</p>
                            <ul class="list-disc list-inside text-sm text-slate-700 dark:text-slate-300 space-y-1">
                                @foreach($profile->preferences as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-slate-400 dark:text-slate-500 italic">Clique em "Atualizar inteligência" para gerar uma visão baseada nas tarefas e documentos do liderado.</p>
                @endif
            </div>
            <div id="profile-sources" class="hidden px-5 pb-5">
                <p class="text-xs uppercase font-semibold text-slate-400 dark:text-slate-500 mb-1">Fontes</p>
                <ul id="profile-sources-list" class="list-disc list-inside text-xs text-slate-500 dark:text-slate-400"></ul>
            </div>
            <p id="profile-error" class="hidden px-5 pb-5 text-xs text-red-700"></p>
        </div>

        <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5 flex items-center justify-between flex-wrap gap-2">
                <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Sugestões de tarefas</h2>
                <div class="flex flex-wrap gap-1" id="suggestion-categories">
                    <button type="button" class="suggestion-category-btn active rounded-full bg-violet-600 px-3 py-1 text-xs font-semibold text-white" data-category="">Todas</button>
                    <button type="button" class="suggestion-category-btn rounded-full bg-slate-100 dark:bg-white/[0.06] px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200" data-category="demanda">Demandas</button>
                    <button type="button" class="suggestion-category-btn rounded-full bg-slate-100 dark:bg-white/[0.06] px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200" data-category="compra">Compras</button>
                    <button type="button" class="suggestion-category-btn rounded-full bg-slate-100 dark:bg-white/[0.06] px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200" data-category="servico">Serviços</button>
                    <button type="button" class="suggestion-category-btn rounded-full bg-slate-100 dark:bg-white/[0.06] px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200" data-category="desenvolvimento">Desenvolvimento</button>
                    <button type="button" class="suggestion-category-btn rounded-full bg-slate-100 dark:bg-white/[0.06] px-3 py-1 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-200" data-category="responsabilidade">Responsabilidades</button>
                </div>
            </div>
            <div id="suggestions-content" class="p-5">
                <p class="text-sm text-slate-400 dark:text-slate-500 italic">Clique em "Sugerir" para gerar tarefas baseadas exclusivamente nos dados registrados.</p>
            </div>
            <div id="suggestions-sources" class="hidden px-5 pb-5">
                <p class="text-xs uppercase font-semibold text-slate-400 dark:text-slate-500 mb-1">Fontes</p>
                <ul id="suggestions-sources-list" class="list-disc list-inside text-xs text-slate-500 dark:text-slate-400"></ul>
            </div>
            <p id="suggestions-error" class="hidden px-5 pb-5 text-xs text-red-700"></p>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Documentos</h2>
        </div>
        <div class="p-5">
            <form method="POST" action="{{ route('team.profile.documents.store', $user) }}" enctype="multipart/form-data" class="mb-5 flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="file" name="document" accept=".txt,.md,.pdf,.doc,.docx,.csv" required
                       class="flex-1 rounded-lg border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-700 dark:text-slate-300 file:mr-3 file:rounded file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-brand-700"/>
                <input type="text" name="name" placeholder="Nome opcional" maxlength="255"
                       class="rounded-lg border border-slate-300 dark:border-white/10 px-3 py-2 text-sm text-slate-700 dark:text-slate-300"/>
                <button type="submit" class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                    Anexar
                </button>
            </form>

            @if($documents->count())
                <ul class="divide-y divide-slate-100 dark:divide-white/5">
                    @foreach($documents as $document)
                        <li class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-100">{{ $document->name }}</p>
                                <p class="text-xs text-slate-400 dark:text-slate-500">
                                    {{ $document->mime_type }} · {{ $document->size ? number_format($document->size / 1024, 1).' KB' : '—' }}
                                    · <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase
                                        @if($document->processing_status === 'pronto') bg-green-50 text-green-700
                                        @elseif($document->processing_status === 'processando') bg-amber-50 text-amber-700
                                        @elseif($document->processing_status === 'needs_ocr') bg-orange-50 text-orange-700
                                        @else bg-red-50 text-red-700
                                        @endif">
                                        {{ str_replace('_', ' ', $document->processing_status) }}
                                    </span>
                                    @if($document->processing_error)
                                        <span class="text-red-600">· {{ Str::limit($document->processing_error, 60) }}</span>
                                    @endif
                                </p>
                            </div>
                            <form method="POST" action="{{ route('team.profile.documents.destroy', [$user, $document->id]) }}" onsubmit="return confirm('Remover este documento?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:text-red-700 font-medium">Remover</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4">
                    {{ $documents->links() }}
                </div>
            @else
                <p class="text-sm text-slate-400 dark:text-slate-500">Nenhum documento anexado.</p>
            @endif
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-white/[0.05] backdrop-blur border border-slate-200 dark:border-white/10 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-white/5">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Comparação de carga</h2>
        </div>
        <div class="p-5">
            <div class="space-y-3">
                @foreach($workload as $member)
                    <div class="flex items-center gap-3">
                        <span class="w-32 text-sm text-slate-600 dark:text-slate-400 truncate">{{ $member['name'] }}</span>
                        <div class="flex-1 h-2 rounded-full bg-slate-100 dark:bg-white/[0.06] overflow-hidden">
                            @php
                                $max = max(1, $workload->max('active_tasks'));
                                $pct = min(100, ($member['active_tasks'] / $max) * 100);
                            @endphp
                            <div class="h-full rounded-full {{ $member['name'] === $user->name ? 'bg-brand-500' : 'bg-slate-300' }}" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="w-12 text-right text-sm font-medium text-slate-700 dark:text-slate-300">{{ $member['active_tasks'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
    const createElement = (tag, classes, text = null) => {
        const element = document.createElement(tag);
        element.className = classes;
        if (text !== null) element.textContent = text;
        return element;
    };

    const renderSources = (list, box, sources) => {
        list.replaceChildren(...sources.map((source) => createElement('li', '', source)));
        box.classList.toggle('hidden', sources.length === 0);
    };

    (function () {
        const toggle = document.getElementById('edit-profile-toggle');
        const readBox = document.getElementById('profile-read');
        const form = document.getElementById('profile-form');
        const cancel = document.getElementById('cancel-profile-edit');

        toggle.addEventListener('click', () => {
            readBox.classList.add('hidden');
            form.classList.remove('hidden');
        });

        cancel.addEventListener('click', () => {
            form.classList.add('hidden');
            readBox.classList.remove('hidden');
        });
    })();

    (function () {
        const btn = document.getElementById('generate-profile');
        const content = document.getElementById('profile-content');
        const sourcesBox = document.getElementById('profile-sources');
        const sourcesList = document.getElementById('profile-sources-list');
        const errorBox = document.getElementById('profile-error');

        btn.addEventListener('click', () => {
            btn.disabled = true;
            btn.textContent = '✨ Gerando...';
            errorBox.classList.add('hidden');

            window.axios.post('{{ route('team.profile.summary', $user) }}')
                .then(({ data }) => {
                    const p = data.profile;
                    const nodes = [];
                    if (p.summary) {
                        nodes.push(createElement('p', 'text-sm text-slate-700 dark:text-slate-300 mb-4', p.summary));
                    }
                    [['Pontos fortes', p.strengths], ['Oportunidades', p.gaps], ['Preferências', p.preferences]].forEach(([label, values]) => {
                        if (!values?.length) return;
                        const section = createElement('div', label === 'Preferências' ? '' : 'mb-3');
                        section.append(createElement('p', 'text-xs uppercase font-semibold text-slate-400 dark:text-slate-500 mb-1', label));
                        const list = createElement('ul', 'list-disc list-inside text-sm text-slate-700 dark:text-slate-300');
                        list.replaceChildren(...values.map((value) => createElement('li', '', value)));
                        section.append(list);
                        nodes.push(section);
                    });
                    content.replaceChildren(...(nodes.length ? nodes : [createElement('p', 'text-sm text-slate-600 dark:text-slate-400', 'Análise gerada.')]));
                    renderSources(sourcesList, sourcesBox, data.sources || []);
                })
                .catch((err) => {
                    errorBox.textContent = err.response?.data?.message ?? 'Não foi possível gerar a análise.';
                    errorBox.classList.remove('hidden');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = '✨ Atualizar inteligência';
                });
        });
    })();

    (function () {
        const btn = document.getElementById('suggest-tasks');
        const content = document.getElementById('suggestions-content');
        const sourcesBox = document.getElementById('suggestions-sources');
        const sourcesList = document.getElementById('suggestions-sources-list');
        const errorBox = document.getElementById('suggestions-error');
        const categoryBtns = document.querySelectorAll('.suggestion-category-btn');
        let currentCategory = '';

        const categoryLabels = {
            recorrente: 'Recorrente',
            corretiva: 'Corretiva',
            desenvolvimento: 'Desenvolvimento',
            delegacao: 'Delegação',
            outro: 'Outro',
        };

        const periodicityLabels = {
            diaria: 'Diária',
            semanal: 'Semanal',
            quinzenal: 'Quinzenal',
            mensal: 'Mensal',
            unica: 'Única',
        };

        categoryBtns.forEach(b => {
            b.addEventListener('click', () => {
                categoryBtns.forEach(b2 => b2.classList.toggle('active', b2 === b));
                currentCategory = b.dataset.category;
            });
        });

        const makeHiddenInput = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            return input;
        };

        const createSuggestion = (suggestion) => {
            const card = createElement('div', 'rounded-lg border border-slate-100 dark:border-white/5 p-3');
            const header = createElement('div', 'flex items-center justify-between gap-2 mb-1');
            header.append(
                createElement('span', 'text-sm font-semibold text-slate-800 dark:text-slate-100', suggestion.title),
                createElement('span', 'rounded-full bg-slate-100 dark:bg-white/[0.06] px-2 py-0.5 text-[10px] font-semibold text-slate-600 dark:text-slate-400', categoryLabels[suggestion.category] || suggestion.category),
            );
            card.append(
                header,
                createElement('p', 'text-xs text-slate-500 dark:text-slate-400 mb-1', suggestion.objective),
                createElement('p', 'text-xs text-slate-500 dark:text-slate-400 mb-2', `Por que: ${suggestion.reason}`),
            );
            const details = createElement('div', 'flex items-center gap-2 text-[11px] text-slate-400 dark:text-slate-500');
            details.append(
                createElement('span', '', `Tipo: ${suggestion.task_type}`),
                createElement('span', '', '·'),
                createElement('span', '', `Periodicidade: ${periodicityLabels[suggestion.periodicity] || suggestion.periodicity}`),
                createElement('span', '', '·'),
                createElement('span', '', `Prioridade: ${suggestion.priority}`),
            );
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = @json(route('tasks.store'));
            form.className = 'mt-2';
            form.append(
                makeHiddenInput('_token', @json(csrf_token())),
                makeHiddenInput('title', suggestion.title),
                makeHiddenInput('task_type', suggestion.task_type),
                makeHiddenInput('priority', suggestion.priority),
                makeHiddenInput('description', `${suggestion.objective}\n\n${suggestion.reason}`),
                makeHiddenInput('due_at', @json(now()->addDays(7)->format('Y-m-d H:i:s'))),
                makeHiddenInput('assigned_to', @json((string) $user->id)),
            );
            form.append(createElement('button', 'rounded-lg bg-brand-50 border border-brand-200 px-3 py-1 text-xs font-semibold text-brand-700 hover:bg-brand-100 transition-colors', 'Transformar em rascunho'));
            form.lastChild.type = 'submit';
            card.append(details, form);

            return card;
        };

        btn.addEventListener('click', () => {
            btn.disabled = true;
            btn.textContent = '✨ Gerando...';
            errorBox.classList.add('hidden');

            window.axios.post('{{ route('team.profile.suggestions', $user) }}', {
                category: currentCategory || null,
            })
                .then(({ data }) => {
                    if (!data.ok || !data.suggestions.length) {
                        content.replaceChildren(createElement('p', 'text-sm text-slate-600 dark:text-slate-400', 'Nenhuma sugestão encontrada nos dados registrados.'));
                        return;
                    }

                    const list = createElement('div', 'space-y-3');
                    list.replaceChildren(...data.suggestions.map(createSuggestion));
                    content.replaceChildren(list);
                    renderSources(sourcesList, sourcesBox, data.sources || []);
                })
                .catch((err) => {
                    errorBox.textContent = err.response?.data?.message ?? 'Não foi possível gerar sugestões.';
                    errorBox.classList.remove('hidden');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = '✨ Sugerir';
                });
        });
    })();
</script>
@endpush
