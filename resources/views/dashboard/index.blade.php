@extends('layouts.app')

@section('title', 'Painel - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Painel</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('tasks.index', ['status' => '', 'overdue' => 1]) }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-red-200 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Atrasadas</span>
                <span class="flex size-8 items-center justify-center rounded-full {{ $tarefasAtrasadas > 0 ? 'bg-red-100' : 'bg-slate-100' }}">
                    <svg class="size-4 {{ $tarefasAtrasadas > 0 ? 'text-red-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold {{ $tarefasAtrasadas > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $tarefasAtrasadas }}</p>
        </a>

        <a href="{{ route('tasks.index', ['priority' => 'urgente']) }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-orange-200 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Urgentes</span>
                <span class="flex size-8 items-center justify-center rounded-full {{ $tarefasUrgentes > 0 ? 'bg-orange-100' : 'bg-slate-100' }}">
                    <svg class="size-4 {{ $tarefasUrgentes > 0 ? 'text-orange-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold {{ $tarefasUrgentes > 0 ? 'text-orange-600' : 'text-slate-900' }}">{{ $tarefasUrgentes }}</p>
        </a>

        <a href="{{ route('tasks.index', ['due_today' => 1]) }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-brand-200 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Vencem Hoje</span>
                <span class="flex size-8 items-center justify-center rounded-full bg-brand-100">
                    <svg class="size-4 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-brand-500">{{ $vencemHoje }}</p>
        </a>

        <a href="{{ route('tasks.index', ['status' => 'aguardando_aprovacao']) }}" class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm hover:shadow-md hover:border-purple-200 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-slate-500">Aguardando Aprovação</span>
                <span class="flex size-8 items-center justify-center rounded-full bg-purple-100">
                    <svg class="size-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-bold text-purple-600">{{ $aguardandoAprovacao }}</p>
        </a>
    </div>

    <div class="rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-slate-900">Visão por Pessoa</h2>
        </div>

        <div class="lg:hidden space-y-3 p-4">
            @forelse($visaoPorPessoa ?? [] as $item)
                <a href="{{ route('tasks.index', ['assigned_to' => $item['user']->id]) }}" class="block rounded-lg border border-slate-200 p-4 hover:border-brand-200 hover:shadow-sm transition-all">
                    <h3 class="font-semibold text-slate-900">{{ $item['user']->name }}</h3>
                    <div class="mt-2 flex gap-4 text-sm">
                        <span class="text-slate-500">Abertas: <span class="font-semibold text-slate-900">{{ $item['abertas'] }}</span></span>
                        <span class="{{ $item['atrasadas'] > 0 ? 'text-red-600' : 'text-slate-500' }}">Atrasadas: <span class="font-semibold">{{ $item['atrasadas'] }}</span></span>
                    </div>
                </a>
            @empty
                <p class="text-sm text-slate-400 italic text-center py-6">Nenhum liderado cadastrado.</p>
            @endforelse
        </div>

        <div class="hidden lg:block">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50">
                        <th class="px-6 py-3 text-left font-medium text-slate-500">Liderado</th>
                        <th class="px-6 py-3 text-left font-medium text-slate-500">Tarefas abertas</th>
                        <th class="px-6 py-3 text-left font-medium text-slate-500">Atrasadas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($visaoPorPessoa ?? [] as $item)
                        <tr class="hover:bg-slate-50/50 cursor-pointer" onclick="window.location='{{ route('tasks.index', ['assigned_to' => $item['user']->id]) }}'">
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $item['user']->name }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $item['abertas'] }}</td>
                            <td class="px-6 py-4 {{ $item['atrasadas'] > 0 ? 'text-red-600 font-medium' : 'text-slate-500' }}">{{ $item['atrasadas'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-sm text-slate-400 italic">
                                Nenhum liderado cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
