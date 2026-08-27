@extends('layouts.app')

@section('auth', true)
@section('title', 'Entrar - Gestão de Tarefas | MedicalThermo')

@section('content')
<div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 px-4 py-12">
    {{-- Aurora / Mesh Background --}}
    <div class="pointer-events-none absolute inset-0 aurora-mesh"></div>
    <div class="pointer-events-none absolute inset-0 tech-grid opacity-60"></div>

    {{-- Floating Orbs — Glassmorphism depth --}}
    <div class="pointer-events-none absolute -top-28 -left-28 size-[520px] rounded-full bg-gradient-to-br from-brand-400/25 via-brand-500/20 to-transparent blur-[70px] animate-float"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-24 size-[640px] rounded-full bg-gradient-to-tl from-brand-600/25 via-brand-800/20 to-transparent blur-[80px] animate-float-reverse"></div>
    <div class="pointer-events-none absolute top-1/2 left-1/2 size-[720px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gradient-to-br from-white/[0.06] via-transparent to-transparent blur-[50px] animate-aurora"></div>

    {{-- Subtle vignette --}}
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-brand-950/40 via-transparent to-white/[0.03]"></div>

    {{-- Content --}}
    <div class="relative w-full max-w-md">
        {{-- Logo --}}
        <div class="mb-8 flex justify-center animate-fade-in" style="animation-delay: 0ms">
            <div class="relative">
                <div class="pointer-events-none absolute -inset-6 rounded-full bg-white/10 blur-2xl"></div>
                <img src="{{ asset('images/logo-medicalthermo-branco.png') }}" alt="MedicalThermo Engenharia" class="relative h-14 w-auto drop-shadow-[0_2px_16px_rgba(255,255,255,0.18)]">
            </div>
        </div>

        {{-- Glass Card --}}
        <div class="relative overflow-hidden rounded-[20px] glass-dark p-[1px] animate-fade-in-up" style="animation-delay: 120ms">
            {{-- inner highlight border --}}
            <div class="rounded-[19px] bg-white/[0.96] backdrop-blur-xl shadow-[0_20px_60px_rgba(0,0,0,0.35),0_1px_3px_rgba(0,0,0,0.1)]">
                {{-- top accent line --}}
                <div class="h-[3px] w-full bg-gradient-to-r from-brand-600 via-brand-400 to-brand-600 opacity-90"></div>

                <div class="p-8">
                    <div class="mb-7 text-center">
                        <h1 class="text-[22px] font-bold tracking-tight text-brand-900">Gestão de Tarefas</h1>
                        <p class="mt-1.5 text-[13.5px] leading-relaxed text-slate-500">Entre com suas credenciais para continuar</p>
                        <div class="mx-auto mt-3 h-px w-12 bg-gradient-to-r from-transparent via-brand-200 to-transparent"></div>
                    </div>

                    @if(session('error'))
                        <div class="mb-5 rounded-xl bg-red-50/90 backdrop-blur border border-red-200/70 p-3.5 text-sm text-red-700 animate-fade-in shadow-sm">
                            <div class="flex gap-2.5">
                                <svg class="size-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="mb-5 rounded-xl bg-emerald-50/90 backdrop-blur border border-emerald-200/70 p-3.5 text-sm text-emerald-700 animate-fade-in shadow-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-5 rounded-xl bg-red-50/90 backdrop-blur border border-red-200/70 p-3.5 text-sm text-red-700 animate-fade-in shadow-sm">
                            <ul class="list-disc pl-4 space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div class="animate-fade-in-up" style="animation-delay: 220ms">
                            <label for="email" class="block text-[13px] font-semibold tracking-wide text-slate-700">E-mail</label>
                            <div class="relative mt-1.5 group">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                                    <svg class="size-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    class="block w-full rounded-xl border border-slate-200/70 bg-white/80 py-3 pl-11 pr-4 text-[14px] text-slate-900 placeholder-slate-400 shadow-sm backdrop-blur outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-[3px] focus:ring-brand-500/15 focus:shadow-[0_4px_16px_rgba(24,128,192,0.12)]"
                                    placeholder="seu@email.com"
                                />
                            </div>
                        </div>

                        <div class="animate-fade-in-up" style="animation-delay: 300ms">
                            <label for="password" class="block text-[13px] font-semibold tracking-wide text-slate-700">Senha</label>
                            <div class="relative mt-1.5 group">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-brand-500 transition-colors">
                                    <svg class="size-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                                </span>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    required
                                    class="block w-full rounded-xl border border-slate-200/70 bg-white/80 py-3 pl-11 pr-4 text-[14px] text-slate-900 placeholder-slate-400 shadow-sm backdrop-blur outline-none transition-all focus:border-brand-400 focus:bg-white focus:ring-[3px] focus:ring-brand-500/15 focus:shadow-[0_4px_16px_rgba(24,128,192,0.12)]"
                                    placeholder="Sua senha"
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="btn-shine w-full rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 px-4 py-3 text-[14px] font-semibold tracking-wide text-white shadow-[0_8px_20px_rgba(24,128,192,0.35),0_1px_2px_rgba(0,0,0,0.08)] outline-none transition-all hover:from-brand-600 hover:to-brand-700 hover:shadow-[0_10px_28px_rgba(24,128,192,0.42)] hover:-translate-y-[1px] active:translate-y-[0px] active:scale-[0.99] focus:ring-4 focus:ring-brand-500/20 animate-fade-in-up"
                            style="animation-delay: 380ms"
                        >
                            <span class="relative flex items-center justify-center gap-2">
                                Entrar
                                <svg class="size-4 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </span>
                        </button>
                    </form>

                    <div class="mt-6 flex items-center gap-3">
                        <div class="h-px flex-1 bg-gradient-to-r from-transparent to-slate-200"></div>
                        <span class="text-[11px] font-medium tracking-widest text-slate-400 uppercase">Acesso seguro</span>
                        <div class="h-px flex-1 bg-gradient-to-l from-transparent to-slate-200"></div>
                    </div>
                </div>
            </div>
        </div>

        <p class="relative mt-6 text-center text-xs font-medium tracking-wide text-white/60 animate-fade-in" style="animation-delay: 520ms">
            MedicalThermo Engenharia · Sistema interno de gestão de tarefas
        </p>

        {{-- bottom subtle glow --}}
        <div class="pointer-events-none absolute -bottom-6 left-1/2 h-px w-64 -translate-x-1/2 bg-gradient-to-r from-transparent via-white/15 to-transparent"></div>
    </div>
</div>
@endsection
