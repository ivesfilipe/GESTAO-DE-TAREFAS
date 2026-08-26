@extends('layouts.app')

@section('title', 'Nova Tarefa - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Nova Tarefa</h1>

    <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm mb-5">
        <label for="nl-input" class="block text-sm font-semibold text-slate-700">
            Criação inteligente <span class="ml-1 rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-bold uppercase text-violet-700">IA</span>
        </label>
        <p class="mt-1 text-xs text-slate-500">
            Escreva em português e preenchemos o formulário: <em>"Reunião com o time amanhã às 15h urgente"</em>, <em>"Backup toda segunda 08h"</em>.
        </p>
        <div class="mt-3 flex gap-2">
            <input type="text" id="nl-input" placeholder="Ex.: Inspecionar compressores sexta às 10h importante"
                   class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm outline-none focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"/>
            <button type="button" id="nl-interpret" class="rounded-lg bg-violet-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-violet-500 transition-colors whitespace-nowrap">
                Interpretar
            </button>
        </div>
        <div id="nl-preview" class="mt-2 hidden rounded-lg bg-violet-50 border border-violet-200 px-3 py-2 text-xs text-violet-800"></div>
        <div id="nl-error" class="mt-2 hidden rounded-lg bg-red-50 border border-red-200 px-3 py-2 text-xs text-red-700"></div>
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
                <div class="flex items-center justify-between">
                    <label for="description" class="block text-sm font-medium text-slate-700">Descrição</label>
                    <button type="button" id="nl-description"
                            class="inline-flex items-center gap-1 rounded-full bg-violet-50 border border-violet-200 px-2.5 py-1 text-[11px] font-semibold text-violet-700 hover:bg-violet-100 transition-colors">
                        ✨ Sugerir descrição
                    </button>
                </div>
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
        const input = document.getElementById('nl-input');
        const button = document.getElementById('nl-interpret');
        const preview = document.getElementById('nl-preview');
        const errorBox = document.getElementById('nl-error');

        function fillDescription(title, priority) {
            return window.axios.post('/tarefas/descricao', { title, priority })
                .then(({ data }) => {
                    if (!data.ok) return;
                    document.getElementById('description').value = data.description;
                    preview.textContent = 'Descrição gerada pela IA ✓ (edite à vontade)';
                    preview.classList.remove('hidden');
                })
                .catch(() => {});
        }

        function run() {
            const text = input.value.trim();
            if (!text) return;
            button.disabled = true;
            button.textContent = 'Interpretando...';

            window.axios.post('/tarefas/interpretar', { text })
                .then(({ data }) => {
                    if (!data.ok) throw new Error(data.message);
                    document.getElementById('title').value = data.title;
                    document.getElementById('priority').value = data.priority;
                    document.getElementById('due_at').value = data.due_at_local;
                    if (data.recurrence_frequency) {
                        document.getElementById('recurrence_frequency').value = data.recurrence_frequency;
                    }
                    preview.textContent = 'Preenchido: "' + data.title + '" · prazo ' + data.due_at_label + ' · prioridade ' + data.priority + ' · gerando descrição...';
                    preview.classList.remove('hidden');
                    errorBox.classList.add('hidden');
                    return fillDescription(data.title, data.priority);
                })
                .catch((err) => {
                    const message = err.response?.data?.message ?? 'Não foi possível interpretar o texto.';
                    errorBox.textContent = message;
                    errorBox.classList.remove('hidden');
                    preview.classList.add('hidden');
                })
                .finally(() => {
                    button.disabled = false;
                    button.textContent = 'Interpretar';
                });
        }

        const descButton = document.getElementById('nl-description');
        descButton.addEventListener('click', () => {
            const title = document.getElementById('title').value.trim();
            if (!title) {
                errorBox.textContent = 'Preencha o título primeiro para gerar a descrição.';
                errorBox.classList.remove('hidden');
                return;
            }
            descButton.disabled = true;
            descButton.textContent = '✨ Gerando...';
            fillDescription(title, document.getElementById('priority').value).finally(() => {
                descButton.disabled = false;
                descButton.textContent = '✨ Sugerir descrição';
            });
        });

        button.addEventListener('click', run);
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') { e.preventDefault(); run(); }
        });
    })();
</script>
@endpush
