@extends('layouts.app')

@section('title', 'Painel - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 flex items-end justify-between gap-4">
        <div class="animate-fade-in-up">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Painel</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Visão executiva · MedicalThermo Engenharia</p>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs text-slate-400 dark:text-slate-500 animate-fade-in" style="animation-delay: 150ms">
            <span class="size-2 rounded-full bg-emerald-400 animate-glow shadow-sm shadow-emerald-400/40"></span>
            Tempo real
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('tasks.index', ['status' => '', 'overdue' => 1]) }}" class="group relative overflow-hidden rounded-2xl bg-white/80 dark:bg-white/[0.06] backdrop-blur border border-slate-200/60 dark:border-white/10 p-6 shadow-sm hover-lift animate-entrance stagger-1 dark:hover:border-red-500/20 dark:hover:bg-white/[0.08]">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-red-50/70 dark:from-red-500/[0.08] via-transparent to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-10 -right-10 size-24 rounded-full bg-red-100/50 dark:bg-red-500/20 blur-2xl group-hover:bg-red-100/70 dark:group-hover:bg-red-500/25 transition-colors"></div>
            <div class="relative flex items-center justify-between">
                <span class="text-[13px] font-semibold tracking-wide text-slate-500 dark:text-slate-400">Atrasadas</span>
                <span class="flex size-9 items-center justify-center rounded-xl border shadow-sm transition-all group-hover:scale-105 {{ $tarefasAtrasadas > 0 ? 'bg-gradient-to-br from-red-500 to-red-600 text-white border-red-500/20 shadow-red-500/20' : 'bg-slate-100 dark:bg-white/10 border-slate-200 dark:border-white/10 text-slate-400 dark:text-slate-500' }}">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="relative mt-3 text-3xl font-bold tracking-tight {{ $tarefasAtrasadas > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white' }}">{{ $tarefasAtrasadas }}</p>
            <p class="relative mt-1 text-xs font-medium {{ $tarefasAtrasadas > 0 ? 'text-red-500 dark:text-red-400/80' : 'text-slate-400 dark:text-slate-500' }}">{{ $tarefasAtrasadas > 0 ? 'Requer atenção imediata' : 'Nenhuma pendência' }}</p>
            @if($tarefasAtrasadas > 0)
                <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-red-500 to-orange-400"></div>
            @else
                <div class="absolute bottom-0 left-0 h-px w-full bg-gradient-to-r from-transparent via-slate-200/50 dark:via-white/10 to-transparent"></div>
            @endif
        </a>

        <a href="{{ route('tasks.index', ['priority' => 'urgente']) }}" class="group relative overflow-hidden rounded-2xl bg-white/80 dark:bg-white/[0.06] backdrop-blur border border-slate-200/60 dark:border-white/10 p-6 shadow-sm hover-lift animate-entrance stagger-2 dark:hover:border-orange-500/20 dark:hover:bg-white/[0.08]">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-orange-50/70 dark:from-orange-500/[0.08] via-transparent to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-10 -right-10 size-24 rounded-full bg-orange-100/50 dark:bg-orange-500/20 blur-2xl group-hover:bg-orange-100/70 dark:group-hover:bg-orange-500/25 transition-colors"></div>
            <div class="relative flex items-center justify-between">
                <span class="text-[13px] font-semibold tracking-wide text-slate-500 dark:text-slate-400">Urgentes</span>
                <span class="flex size-9 items-center justify-center rounded-xl border shadow-sm transition-all group-hover:scale-105 {{ $tarefasUrgentes > 0 ? 'bg-gradient-to-br from-orange-500 to-amber-500 text-white border-orange-500/20 shadow-orange-500/20' : 'bg-slate-100 dark:bg-white/10 border-slate-200 dark:border-white/10 text-slate-400 dark:text-slate-500' }}">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>
            </div>
            <p class="relative mt-3 text-3xl font-bold tracking-tight {{ $tarefasUrgentes > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-slate-900 dark:text-white' }}">{{ $tarefasUrgentes }}</p>
            <p class="relative mt-1 text-xs font-medium {{ $tarefasUrgentes > 0 ? 'text-orange-500 dark:text-orange-400/80' : 'text-slate-400 dark:text-slate-500' }}">{{ $tarefasUrgentes > 0 ? 'Prioridade crítica' : 'Sem urgências' }}</p>
            @if($tarefasUrgentes > 0)
                <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-orange-500 to-amber-400"></div>
            @else
                <div class="absolute bottom-0 left-0 h-px w-full bg-gradient-to-r from-transparent via-slate-200/50 dark:via-white/10 to-transparent"></div>
            @endif
        </a>

        <a href="{{ route('tasks.index', ['due_today' => 1]) }}" class="group relative overflow-hidden rounded-2xl bg-white/80 dark:bg-white/[0.06] backdrop-blur border border-slate-200/60 dark:border-white/10 p-6 shadow-sm hover-lift animate-entrance stagger-3 dark:hover:border-brand-500/20 dark:hover:bg-white/[0.08]">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-brand-50/80 dark:from-brand-500/[0.08] via-transparent to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-10 -right-10 size-24 rounded-full bg-brand-100/50 dark:bg-brand-500/20 blur-2xl group-hover:bg-brand-100/70 dark:group-hover:bg-brand-500/25 transition-colors"></div>
            <div class="relative flex items-center justify-between">
                <span class="text-[13px] font-semibold tracking-wide text-slate-500 dark:text-slate-400">Vencem Hoje</span>
                <span class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white border border-brand-500/20 shadow-sm shadow-brand-500/20 transition-all group-hover:scale-105">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
            </div>
            <p class="relative mt-3 text-3xl font-bold tracking-tight text-brand-600 dark:text-brand-400">{{ $vencemHoje }}</p>
            <p class="relative mt-1 text-xs font-medium text-brand-500/80 dark:text-brand-300/70">Entregas do dia</p>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-brand-500 to-brand-400"></div>
        </a>

        <a href="{{ route('tasks.index', ['status' => 'aguardando_aprovacao']) }}" class="group relative overflow-hidden rounded-2xl bg-white/80 dark:bg-white/[0.06] backdrop-blur border border-slate-200/60 dark:border-white/10 p-6 shadow-sm hover-lift animate-entrance stagger-4 dark:hover:border-violet-500/20 dark:hover:bg-white/[0.08]">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-violet-50/70 dark:from-violet-500/[0.08] via-transparent to-transparent opacity-60 group-hover:opacity-100 transition-opacity"></div>
            <div class="pointer-events-none absolute -top-10 -right-10 size-24 rounded-full bg-violet-100/50 dark:bg-violet-500/20 blur-2xl group-hover:bg-violet-100/70 dark:group-hover:bg-violet-500/25 transition-colors"></div>
            <div class="relative flex items-center justify-between">
                <span class="text-[13px] font-semibold tracking-wide text-slate-500 dark:text-slate-400">Aguardando Aprovação</span>
                <span class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white border border-violet-500/20 shadow-sm shadow-violet-500/20 transition-all group-hover:scale-105">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="relative mt-3 text-3xl font-bold tracking-tight text-violet-600 dark:text-violet-400">{{ $aguardandoAprovacao }}</p>
            <p class="relative mt-1 text-xs font-medium text-violet-500/80 dark:text-violet-300/70">Fila de revisão</p>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-violet-500 to-purple-400"></div>
        </a>
    </div>

    <div class="rounded-2xl bg-white/85 dark:bg-slate-900/60 backdrop-blur border border-slate-200/60 dark:border-white/10 shadow-sm overflow-hidden animate-entrance stagger-5">
        <div class="border-b border-slate-200/60 dark:border-white/5 px-6 py-4 bg-gradient-to-r from-slate-50/80 via-white to-white dark:from-white/[0.04] dark:via-white/[0.02] dark:to-transparent">
            <div class="flex items-center gap-3">
                <div class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-sm">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-[15px] font-semibold tracking-tight text-slate-900 dark:text-white">Visão por Pessoa</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Carga e pendências por liderado · clique para filtrar</p>
                </div>
                <span class="ml-auto hidden sm:inline-flex items-center rounded-full bg-slate-100 dark:bg-white/10 border border-slate-200 dark:border-white/10 px-2.5 py-1 text-xs font-medium text-slate-600 dark:text-slate-300">{{ count($visaoPorPessoa ?? []) }} liderados</span>
            </div>
        </div>

        <div class="lg:hidden space-y-3 p-4">
            @forelse($visaoPorPessoa ?? [] as $item)
                <a href="{{ route('tasks.index', ['assigned_to' => $item['user']->id]) }}" class="group block rounded-2xl bg-white dark:bg-white/[0.04] border border-slate-200/70 dark:border-white/10 p-4 hover:border-brand-200 dark:hover:border-white/20 hover:shadow-md dark:hover:bg-white/[0.06] hover:-translate-y-0.5 transition-all">
                    <div class="flex items-center gap-3">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white text-xs font-bold shadow-sm">
                            {{ strtoupper(substr($item['user']->name, 0, 2)) }}
                        </div>
                        <h3 class="font-semibold text-slate-900 dark:text-white group-hover:text-brand-700 dark:group-hover:text-brand-300">{{ $item['user']->name }}</h3>
                        <svg class="ml-auto size-4 text-slate-300 dark:text-slate-600 group-hover:text-brand-400 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <div class="mt-3 flex gap-3 text-sm">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 px-3 py-1 text-xs font-medium text-slate-600 dark:text-slate-300">Abertas <b class="text-slate-900 dark:text-white">{{ $item['abertas'] }}</b></span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium border {{ $item['atrasadas'] > 0 ? 'bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-300' : 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-300' }}">{{ $item['atrasadas'] > 0 ? '⚠ ' : '✓ ' }} Atrasadas <b>{{ $item['atrasadas'] }}</b></span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 dark:border-white/10 p-8 text-center bg-slate-50/50 dark:bg-white/[0.02]">
                    <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-400">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <p class="mt-3 text-sm font-medium text-slate-600 dark:text-slate-300">Nenhum liderado cadastrado</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">Adicione membros à equipe para ver a distribuição.</p>
                </div>
            @endforelse
        </div>

        <div class="hidden lg:block">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200/60 dark:border-white/5 bg-slate-50/70 dark:bg-white/[0.03]">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Liderado</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Tarefas abertas</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Atrasadas</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/70 dark:divide-white/5">
                    @forelse($visaoPorPessoa ?? [] as $item)
                        <tr class="group hover:bg-gradient-to-r hover:from-brand-50/50 dark:hover:from-white/[0.04] hover:to-transparent cursor-pointer transition-colors" onclick="window.location='{{ route('tasks.index', ['assigned_to' => $item['user']->id]) }}'">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 dark:from-slate-700 dark:to-slate-800 text-white text-[11px] font-bold shadow-sm group-hover:from-brand-600 group-hover:to-brand-700 transition-all">
                                        {{ strtoupper(substr($item['user']->name, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-slate-900 dark:text-white group-hover:text-brand-700 dark:group-hover:text-brand-300">{{ $item['user']->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-slate-100 dark:bg-white/10 border border-slate-200 dark:border-white/10 px-3 py-1 text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $item['abertas'] }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($item['atrasadas'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 dark:bg-red-500/15 border border-red-200 dark:border-red-500/30 px-3 py-1 text-xs font-semibold text-red-700 dark:text-red-300"><span class="size-1.5 rounded-full bg-red-500 animate-glow"></span>{{ $item['atrasadas'] }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300">● 0</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-400 dark:text-slate-500 group-hover:text-brand-600 dark:group-hover:text-brand-300 group-hover:gap-1.5 transition-all">Ver tarefas <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-400">
                                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <p class="mt-3 text-sm font-medium text-slate-600 dark:text-slate-300">Nenhum liderado cadastrado</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
