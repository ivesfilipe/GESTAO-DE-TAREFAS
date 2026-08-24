@extends('layouts.app')

@section('title', 'Equipe - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Equipe</h1>
        <button
            type="button"
            onclick="document.getElementById('invite-form').classList.toggle('hidden')"
            class="inline-flex items-center gap-2 rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors"
        >
            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Convidar liderado
        </button>
    </div>

    <div id="invite-form" class="hidden mb-6 rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Convidar novo liderado</h2>
        <form method="POST" action="{{ route('team.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nome</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                        placeholder="Nome completo"
                    />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">E-mail</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none"
                        placeholder="email@exemplo.com"
                    />
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <input type="hidden" name="role" value="liderado">
            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-brand-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors">
                    Enviar convite
                </button>
                <button type="button" onclick="document.getElementById('invite-form').classList.add('hidden')" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
            </div>
        </form>
    </div>

    @if(session('invite_link'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4">
            <p class="text-sm font-medium text-green-800 mb-2">Convite enviado! Compartilhe este link com o liderado:</p>
            <div class="flex items-center gap-2">
                <code class="flex-1 rounded bg-white px-3 py-2 text-sm text-slate-700 border border-green-200 break-all">{{ session('invite_link') }}</code>
                <button
                    type="button"
                    onclick="navigator.clipboard.writeText('{{ session('invite_link') }}')"
                    class="rounded-lg bg-white border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50"
                >
                    Copiar
                </button>
            </div>
        </div>
    @endif

    <div class="lg:hidden space-y-3">
        @forelse($liderados as $member)
            <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $member->name }}</h3>
                        <p class="text-sm text-slate-500">{{ $member->email }}</p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $member->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>
                <div class="mt-3 flex items-center gap-4">
                    <form method="POST" action="{{ route('team.regenerate-invite', $member) }}">
                        @csrf
                        <button type="submit" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
                            Novo link de acesso
                        </button>
                    </form>
                    <form method="POST" action="{{ route('team.toggle-active', $member) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-sm {{ $member->is_active ? 'text-red-600 hover:text-red-700' : 'text-green-600 hover:text-green-700' }} font-medium">
                            {{ $member->is_active ? 'Desativar' : 'Ativar' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('team.destroy', $member) }}" onsubmit="return confirm('Excluir {{ $member->name }}? Esta ação remove o acesso dele(a) ao sistema.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-slate-400 hover:text-red-600 font-medium">
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-white border border-slate-200 p-12 text-center shadow-sm">
                <svg class="mx-auto size-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <h3 class="mt-3 text-sm font-medium text-slate-900">Nenhum liderado</h3>
                <p class="mt-1 text-sm text-slate-500">Convide liderados para começar a delegar tarefas.</p>
            </div>
        @endforelse
    </div>

    <div class="hidden lg:block rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50">
                    <th class="px-6 py-3 text-left font-medium text-slate-500">Nome</th>
                    <th class="px-6 py-3 text-left font-medium text-slate-500">Email</th>
                    <th class="px-6 py-3 text-left font-medium text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right font-medium text-slate-500">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($liderados as $member)
                    <tr class="hover:bg-slate-50/50">
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $member->name }}</td>
                        <td class="px-6 py-4 text-slate-500">{{ $member->email }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $member->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-4">
                                <form method="POST" action="{{ route('team.regenerate-invite', $member) }}">
                                    @csrf
                                    <button type="submit" class="text-brand-600 hover:text-brand-700 text-sm font-medium">
                                        Novo link de acesso
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('team.toggle-active', $member) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="{{ $member->is_active ? 'text-red-600 hover:text-red-700' : 'text-green-600 hover:text-green-700' }} text-sm font-medium">
                                        {{ $member->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('team.destroy', $member) }}" onsubmit="return confirm('Excluir {{ $member->name }}? Esta ação remove o acesso dele(a) ao sistema.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-600 text-sm font-medium">
                                        Excluir
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <svg class="mx-auto size-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <h3 class="mt-3 text-sm font-medium text-slate-900">Nenhum liderado</h3>
                            <p class="mt-1 text-sm text-slate-500">Convide liderados para começar a delegar tarefas.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
