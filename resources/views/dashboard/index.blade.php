@extends('layouts.app')

@section('title', 'Painel - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 flex items-end justify-between gap-4">
        <div class="animate-fade-in-up">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Painel</h1>
            <p class="mt-1 text-sm text-slate-500">Visão executiva · MedicalThermo Engenharia</p>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400 animate-fade-in" style="animation-delay: 150ms">
            <span class="size-2 rounded-full bg-emerald-400 animate-glow"></span>
            Tempo real
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('tasks.index', ['status' => '', 'overdue' => 1]) }}" class="group relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur border border-slate-200/60 p-6 shadow-sm hover-lift animate-entrance stagger-1">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-red-50/70 via-transparent to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-10 -right-10 size-24 rounded-full bg-red-100/50 blur-2xl group-hover:bg-red-100/70 transition-colors"></div>
            <div class="relative flex items-center justify-between">
                <span class="text-[13px] font-semibold tracking-wide text-slate-500">Atrasadas</span>
                <span class="flex size-9 items-center justify-center rounded-xl border shadow-sm transition-all group-hover:scale-105 {{ $tarefasAtrasadas > 0 ? 'bg-gradient-to-br from-red-500 to-red-600 text-white border-red-500/20 shadow-red-500/20' : 'bg-slate-100 border-slate-200 text-slate-400' }}">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="relative mt-3 text-3xl font-bold tracking-tight {{ $tarefasAtrasadas > 0 ? 'text-red-600' : 'text-slate-900' }}">{{ $tarefasAtrasadas }}</p>
            <p class="relative mt-1 text-xs font-medium {{ $tarefasAtrasadas > 0 ? 'text-red-500' : 'text-slate-400' }}">{{ $tarefasAtrasadas > 0 ? 'Requer atenção imediata' : 'Nenhuma pendência' }}</p>
            @if($tarefasAtrasadas > 0)
                <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-red-500 to-orange-400"></div>
            @endif
        </a>

        <a href="{{ route('tasks.index', ['priority' => 'urgente']) }}" class="group relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur border border-slate-200/60 p-6 shadow-sm hover-lift animate-entrance stagger-2">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-orange-50/70 via-transparent to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-10 -right-10 size-24 rounded-full bg-orange-100/50 blur-2xl group-hover:bg-orange-100/70 transition-colors"></div>
            <div class="relative flex items-center justify-between">
                <span class="text-[13px] font-semibold tracking-wide text-slate-500">Urgentes</span>
                <span class="flex size-9 items-center justify-center rounded-xl border shadow-sm transition-all group-hover:scale-105 {{ $tarefasUrgentes > 0 ? 'bg-gradient-to-br from-orange-500 to-amber-500 text-white border-orange-500/20 shadow-orange-500/20' : 'bg-slate-100 border-slate-200 text-slate-400' }}">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>
            </div>
            <p class="relative mt-3 text-3xl font-bold tracking-tight {{ $tarefasUrgentes > 0 ? 'text-orange-600' : 'text-slate-900' }}">{{ $tarefasUrgentes }}</p>
            <p class="relative mt-1 text-xs font-medium {{ $tarefasUrgentes > 0 ? 'text-orange-500' : 'text-slate-400' }}">{{ $tarefasUrgentes > 0 ? 'Prioridade crítica' : 'Sem urgências' }}</p>
            @if($tarefasUrgentes > 0)
                <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-orange-500 to-amber-400"></div>
            @endif
        </a>

        <a href="{{ route('tasks.index', ['due_today' => 1]) }}" class="group relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur border border-slate-200/60 p-6 shadow-sm hover-lift animate-entrance stagger-3">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-brand-50/80 via-transparent to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-10 -right-10 size-24 rounded-full bg-brand-100/50 blur-2xl group-hover:bg-brand-100/70 transition-colors"></div>
            <div class="relative flex items-center justify-between">
                <span class="text-[13px] font-semibold tracking-wide text-slate-500">Vencem Hoje</span>
                <span class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white border border-brand-500/20 shadow-sm shadow-brand-500/20 transition-all group-hover:scale-105">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
            </div>
            <p class="relative mt-3 text-3xl font-bold tracking-tight text-brand-600">{{ $vencemHoje }}</p>
            <p class="relative mt-1 text-xs font-medium text-brand-500/80">Entregas do dia</p>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-brand-500 to-brand-400"></div>
        </a>

        <a href="{{ route('tasks.index', ['status' => 'aguardando_aprovacao']) }}" class="group relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur border border-slate-200/60 p-6 shadow-sm hover-lift animate-entrance stagger-4">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-violet-50/70 via-transparent to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-10 -right-10 size-24 rounded-full bg-violet-100/50 blur-2xl group-hover:bg-violet-100/70 transition-colors"></div>
            <div class="relative flex items-center justify-between">
                <span class="text-[13px] font-semibold tracking-wide text-slate-500">Aguardando Aprovação</span>
                <span class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white border border-violet-500/20 shadow-sm shadow-violet-500/20 transition-all group-hover:scale-105">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="relative mt-3 text-3xl font-bold tracking-tight text-violet-600">{{ $aguardandoAprovacao }}</p>
            <p class="relative mt-1 text-xs font-medium text-violet-500/80">Fila de revisão</p>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-violet-500 to-purple-400"></div>
        </a>
    </div>

    <div class="rounded-2xl bg-white/85 backdrop-blur border border-slate-200/60 shadow-sm overflow-hidden animate-entrance stagger-5">
        <div class="border-b border-slate-200/60 px-6 py-4 bg-gradient-to-r from-slate-50/80 via-white to-white">
            <div class="flex items-center gap-3">
                <div class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-sm">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-[15px] font-semibold tracking-tight text-slate-900">Visão por Pessoa</h2>
                    <p class="text-xs text-slate-500">Carga e pendências por liderado · clique para filtrar</p>
                </div>
                <span class="ml-auto hidden sm:inline-flex items-center rounded-full bg-slate-100 border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-600">{{ count($visaoPorPessoa ?? []) }} liderados</span>
            </div>
        </div>

        <div class="lg:hidden space-y-3 p-4">
            @forelse($visaoPorPessoa ?? [] as $item)
                <a href="{{ route('tasks.index', ['assigned_to' => $item['user']->id]) }}" class="group block rounded-2xl bg-white border border-slate-200/70 p-4 hover:border-brand-200 hover:shadow-md hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white text-xs font-bold shadow-sm">
                            {{ strtoupper(substr($item['user']->name, 0, 2)) }}
                        </div>
                        <h3 class="font-semibold text-slate-900 group-hover:text-brand-700">{{ $item['user']->name }}</h3>
                        <svg class="ml-auto size-4 text-slate-300 group-hover:text-brand-400 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div class="mt-3 flex gap-3 text-sm">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 border border-slate-200 px-3 py-1 text-xs font-medium text-slate-600">Abertas <b class="text-slate-900">{{ $item['abertas'] }}</b></span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium border {{ $item['atrasadas'] > 0 ? 'bg-red-50 border-red-200 text-red-700' : 'bg-emerald-50 border-emerald-200 text-emerald-700' }}">{{ $item['atrasadas'] > 0 ? '⚠ ' : '✓ ' }} Atrasadas <b>{{ $item['atrasadas'] }}</b></span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-50 border border-slate-200 text-slate-400">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="mt-3 text-sm font-medium text-slate-600">Nenhum liderado cadastrado</p>
                    <p class="text-xs text-slate-400">Adicione membros à equipe para ver a distribuição.</p>
                </div>
            @endforelse
        </div>

        <div class="hidden lg:block">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200/60 bg-slate-50/70">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-500">Liderado</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-500">Tarefas abertas</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-500">Atrasadas</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-widest text-slate-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/70">
                    @forelse($visaoPorPessoa ?? [] as $item)
                        <tr class="group hover:bg-gradient-to-r hover:from-brand-50/50 hover:to-transparent cursor-pointer transition-colors" onclick="window.location='{{ route('tasks.index', ['assigned_to' => $item['user']->id]) }}'">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 text-white text-[11px] font-bold shadow-sm group-hover:from-brand-600 group-hover:to-brand-700 transition-all">
                                        {{ strtoupper(substr($item['user']->name, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-slate-900 group-hover:text-brand-700">{{ $item['user']->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">{{ $item['abertas'] }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($item['atrasadas'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 border border-red-200 px-3 py-1 text-xs font-semibold text-red-700"><span class="size-1.5 rounded-full bg-red-500 animate-glow"></span>{{ $item['atrasadas'] }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-700">● 0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 group-hover:text-brand-600 group-hover:gap-1.5 transition-all">Ver tarefas <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-50 border border-slate-200 text-slate-400">
                                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <p class="mt-3 text-sm font-medium text-slate-600">Nenhum liderado cadastrado</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
