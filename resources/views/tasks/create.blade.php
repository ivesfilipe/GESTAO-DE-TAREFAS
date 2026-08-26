@extends('layouts.app')

@section('title', 'Nova Tarefa - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Nova Tarefa</h1>

    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm mb-5">
        <div class="flex items-center gap-2 mb-2">
            <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700">IA</span>
            <label for="ai-delegate-input" class="block text-sm font-semibold text-slate-700">Delegar com IA</label>
        </div>
        <p class="text-xs text-slate-500 mb-3">
            Descreva a tarefa em linguagem natural e a IA monta um rascunho. Exemplo: <em>"Revisar contrato do fornecedor até sexta às 17h, urgente"</em>.
        </p>

        <div class="space-y-3">
            <textarea
                id="ai-delegate-input"
                rows="3"
                maxlength="1000"
                class="block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none resize-none"
                placeholder="Descreva o que precisa ser feito..."
            ></textarea>

            <div class="flex flex-col sm:flex-row gap-3">
                <select
                    id="ai-delegate-assignee"
                    class="sm:flex-1 rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                >
                    <option value="">Responsável (opcional)</option>
                    @foreach($liderados ?? [] as $liderado)
                        <option value="{{ $liderado->id }}">{{ $liderado->name }}</option>
                    @endforeach
                </select>

                <button type="button" id="ai-delegate-btn"
                        class="w-full sm:w-auto rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-violet-500 transition-colors whitespace-nowrap">
                    Gerar tarefa inteligente
                </button>
            </div>
        </div>

        <div id="ai-delegate-error" class="hidden mt-3 rounded-lg bg-red-50 border border-red-200 px-3 py-2 text-xs text-red-700"></div>

        <div id="ai-delegate-draft" class="hidden mt-4 rounded-xl bg-violet-50 border border-violet-200 p-4 space-y-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700">Rascunho IA</span>
                    <span id="draft-confidence" class="text-xs font-medium text-slate-600"></span>
                </div>
                <span id="draft-provider" class="text-xs text-slate-400"></span>
            </div>

            <div id="draft-fallback" class="hidden rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-xs text-amber-800"></div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-lg bg-white border border-violet-100 p-3">
                    <p class="text-[11px] uppercase font-semibold text-slate-400">Título</p>
                    <p id="draft-title" class="text-sm font-medium text-slate-800"></p>
                </div>
                <div class="rounded-lg bg-white border border-violet-100 p-3">
                    <p class="text-[11px] uppercase font-semibold text-slate-400">Tipo</p>
                    <p id="draft-type" class="text-sm font-medium text-slate-800"></p>
                </div>
                <div class="rounded-lg bg-white border border-violet-100 p-3">
                    <p class="text-[11px] uppercase font-semibold text-slate-400">Prioridade</p>
                    <p id="draft-priority" class="text-sm font-medium text-slate-800"></p>
                </div>
                <div class="rounded-lg bg-white border border-violet-100 p-3">
                    <p class="text-[11px] uppercase font-semibold text-slate-400">Prazo sugerido</p>
                    <p id="draft-due" class="text-sm font-medium text-slate-800"></p>
                </div>
                <div class="rounded-lg bg-white border border-violet-100 p-3 sm:col-span-2">
                    <p class="text-[11px] uppercase font-semibold text-slate-400">Responsável sugerido</p>
                    <p id="draft-assignee" class="text-sm font-medium text-slate-800"></p>
                    <p id="draft-assignee-reason" class="text-xs text-slate-500 mt-1"></p>
                </div>
            </div>

            <div class="rounded-lg bg-white border border-violet-100 p-3">
                <p class="text-[11px] uppercase font-semibold text-slate-400 mb-1">Descrição</p>
                <p id="draft-description" class="text-sm text-slate-700 whitespace-pre-line"></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-lg bg-white border border-violet-100 p-3">
                    <p class="text-[11px] uppercase font-semibold text-slate-400 mb-1">Critérios de aceitação</p>
                    <ul id="draft-criteria" class="list-disc list-inside text-sm text-slate-700 space-y-1"></ul>
                </div>
                <div class="rounded-lg bg-white border border-violet-100 p-3">
                    <p class="text-[11px] uppercase font-semibold text-slate-400 mb-1">Evidências esperadas</p>
                    <ul id="draft-evidence" class="list-disc list-inside text-sm text-slate-700 space-y-1"></ul>
                </div>
            </div>

            <div class="rounded-lg bg-white border border-violet-100 p-3">
                <p class="text-[11px] uppercase font-semibold text-slate-400 mb-1">Checkpoints</p>
                <ul id="draft-checkpoints" class="list-disc list-inside text-sm text-slate-700 space-y-1"></ul>
            </div>

            <div id="draft-missing-box" class="hidden rounded-lg bg-white border border-violet-100 p-3">
                <p class="text-[11px] uppercase font-semibold text-slate-400 mb-1">Informações faltantes</p>
                <ul id="draft-missing" class="list-disc list-inside text-sm text-slate-700 space-y-1"></ul>
            </div>

            <button type="button" id="ai-delegate-apply"
                    class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-500 transition-colors">
                Aplicar ao formulário
            </button>
        </div>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <form method="POST" action="{{ route('tasks.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="title" class="block text-sm font-medium text-slate-700">Título <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    name="title"
                    id="title"
                    value="{{ old('title') }}"
                    required
                    autofocus
                    class="mt-1 block w-full rounded-lg border {{ $errors->has('title') ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-500/20' }} px-4 py-2.5 text-sm text-slate-900 outline-none focus:ring-2"
                    placeholder="Título da tarefa"
                />
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-slate-700">Descrição</label>
                <textarea
                    name="description"
                    id="description"
                    rows="4"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                    placeholder="Descreva a tarefa (opcional)"
                >{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-slate-700">Responsável</label>
                    <select
                        name="assigned_to"
                        id="assigned_to"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                    >
                        <option value="">Sem responsável</option>
                        @foreach($liderados ?? [] as $liderado)
                            <option value="{{ $liderado->id }}" {{ old('assigned_to') == $liderado->id ? 'selected' : '' }}>
                                {{ $liderado->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-slate-700">Prioridade</label>
                    <select
                        name="priority"
                        id="priority"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                    >
                        <option value="normal" {{ old('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="importante" {{ old('priority') === 'importante' ? 'selected' : '' }}>Importante</option>
                        <option value="urgente" {{ old('priority') === 'urgente' ? 'selected' : '' }}>Urgente</option>
                        <option value="critica" {{ old('priority') === 'critica' ? 'selected' : '' }}>Crítica</option>
                    </select>
                    @error('priority')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="due_at" class="block text-sm font-medium text-slate-700">Prazo <span class="text-red-500">*</span></label>
                    <input
                        type="datetime-local"
                        name="due_at"
                        id="due_at"
                        value="{{ old('due_at') }}"
                        required
                        class="mt-1 block w-full rounded-lg border {{ $errors->has('due_at') ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20' : 'border-slate-300 focus:border-brand-500 focus:ring-brand-500/20' }} px-4 py-2.5 text-sm text-slate-900 outline-none focus:ring-2"
                    />
                    @error('due_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="recurrence_frequency" class="block text-sm font-medium text-slate-700">Repetir</label>
                    <select
                        name="recurrence_frequency"
                        id="recurrence_frequency"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                    >
                        <option value="">Não repetir</option>
                        <option value="diaria" {{ old('recurrence_frequency') === 'diaria' ? 'selected' : '' }}>Todos os dias</option>
                        <option value="semanal" {{ old('recurrence_frequency') === 'semanal' ? 'selected' : '' }}>Toda semana</option>
                        <option value="quinzenal" {{ old('recurrence_frequency') === 'quinzenal' ? 'selected' : '' }}>A cada 2 semanas</option>
                        <option value="mensal" {{ old('recurrence_frequency') === 'mensal' ? 'selected' : '' }}>Todo mês</option>
                    </select>
                    <p class="mt-1 text-xs text-slate-400">A próxima tarefa é criada automaticamente com o mesmo prazo/cadência.</p>
                    @error('recurrence_frequency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="task_type" class="block text-sm font-medium text-slate-700">Tipo</label>
                    <select
                        name="task_type"
                        id="task_type"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                    >
                        @foreach(\App\Models\Task::taskTypes() as $type)
                            <option value="{{ $type }}" {{ old('task_type', 'demanda') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    @error('task_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="acceptance_criteria" class="block text-sm font-medium text-slate-700">Critérios de aceitação</label>
                <textarea
                    name="acceptance_criteria"
                    id="acceptance_criteria"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                    placeholder="O que deve ser verdadeiro para considerar esta tarefa concluída?"
                >{{ old('acceptance_criteria') }}</textarea>
                @error('acceptance_criteria')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="expected_evidence" class="block text-sm font-medium text-slate-700">Evidências esperadas</label>
                <textarea
                    name="expected_evidence"
                    id="expected_evidence"
                    rows="3"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                    placeholder="O que deve ser entregue/anexado como prova de conclusão?"
                >{{ old('expected_evidence') }}</textarea>
                @error('expected_evidence')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('tasks.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="rounded-lg bg-brand-700 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-colors"
                >
                    Criar Tarefa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('ai-delegate-input');
        const assigneeSelect = document.getElementById('ai-delegate-assignee');
        const btn = document.getElementById('ai-delegate-btn');
        const draftBox = document.getElementById('ai-delegate-draft');
        const errorBox = document.getElementById('ai-delegate-error');
        const applyBtn = document.getElementById('ai-delegate-apply');

        let currentDraft = null;

        function formatDateTimeLocal(iso) {
            const d = new Date(iso);
            if (isNaN(d.getTime())) return '';
            const pad = n => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
        }

        function renderList(elementId, items) {
            const el = document.getElementById(elementId);
            el.innerHTML = '';
            (items || []).forEach(item => {
                const li = document.createElement('li');
                li.textContent = item;
                el.appendChild(li);
            });
        }

        function showDraft(draft) {
            currentDraft = draft;

            document.getElementById('draft-title').textContent = draft.title || '—';
            document.getElementById('draft-type').textContent = draft.task_type ? draft.task_type.charAt(0).toUpperCase() + draft.task_type.slice(1) : '—';
            document.getElementById('draft-priority').textContent = draft.priority ? draft.priority.charAt(0).toUpperCase() + draft.priority.slice(1) : '—';
            document.getElementById('draft-due').textContent = draft.due_at
                ? new Date(draft.due_at).toLocaleString('pt-BR')
                : '—';
            document.getElementById('draft-assignee').textContent = draft.recommended_assignee_name || 'Sem sugestão';
            document.getElementById('draft-assignee-reason').textContent = draft.assignee_reason || '';
            document.getElementById('draft-description').textContent = draft.description || '';
            document.getElementById('draft-confidence').textContent = draft.confidence ? `Confiança: ${draft.confidence}` : '';
            document.getElementById('draft-provider').textContent = draft.ai_mock ? `${draft.ai_provider} (simulação)` : draft.ai_provider;

            renderList('draft-criteria', draft.acceptance_criteria);
            renderList('draft-evidence', draft.expected_evidence);
            renderList('draft-checkpoints', draft.checkpoints);

            const missingBox = document.getElementById('draft-missing-box');
            if (draft.missing_information && draft.missing_information.length) {
                missingBox.classList.remove('hidden');
                renderList('draft-missing', draft.missing_information);
            } else {
                missingBox.classList.add('hidden');
            }

            const fallback = document.getElementById('draft-fallback');
            if (draft.fallback_message) {
                fallback.textContent = draft.fallback_message;
                fallback.classList.remove('hidden');
            } else {
                fallback.classList.add('hidden');
            }

            draftBox.classList.remove('hidden');
        }

        btn.addEventListener('click', () => {
            const text = input.value.trim();
            if (!text) {
                errorBox.textContent = 'Descreva a tarefa primeiro.';
                errorBox.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.textContent = 'Gerando...';
            errorBox.classList.add('hidden');

            window.axios.post('{{ route('tasks.smart-delegate') }}', {
                input: text,
                assigned_to: assigneeSelect.value || null,
            })
                .then(({ data }) => {
                    if (!data.ok) throw new Error(data.message);
                    showDraft(data.draft);
                })
                .catch((err) => {
                    errorBox.textContent = err.response?.data?.message ?? 'Não foi possível gerar o rascunho.';
                    errorBox.classList.remove('hidden');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = 'Gerar tarefa inteligente';
                });
        });

        applyBtn.addEventListener('click', () => {
            if (!currentDraft) return;

            document.getElementById('title').value = currentDraft.title || '';
            document.getElementById('description').value = currentDraft.description || '';
            document.getElementById('task_type').value = currentDraft.task_type || 'demanda';
            document.getElementById('priority').value = currentDraft.priority || 'normal';

            if (currentDraft.due_at) {
                document.getElementById('due_at').value = formatDateTimeLocal(currentDraft.due_at);
            }

            if (currentDraft.recurrence_frequency) {
                document.getElementById('recurrence_frequency').value = currentDraft.recurrence_frequency;
            }

            if (currentDraft.recommended_assignee_id) {
                document.getElementById('assigned_to').value = currentDraft.recommended_assignee_id;
            }

            if (currentDraft.acceptance_criteria && currentDraft.acceptance_criteria.length) {
                document.getElementById('acceptance_criteria').value = currentDraft.acceptance_criteria.map((c, i) => `${i + 1}. ${c}`).join('\n');
            }

            if (currentDraft.expected_evidence && currentDraft.expected_evidence.length) {
                document.getElementById('expected_evidence').value = currentDraft.expected_evidence.map((e, i) => `${i + 1}. ${e}`).join('\n');
            }

            draftBox.classList.add('hidden');
            window.scrollTo({ top: document.getElementById('title').offsetTop - 100, behavior: 'smooth' });
        });
    })();
</script>
@endpush
