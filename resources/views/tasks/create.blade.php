@extends('layouts.app')

@section('title', 'Nova Tarefa - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Nova Tarefa</h1>

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

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
