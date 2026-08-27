@extends('layouts.app')

@section('auth', true)
@section('title', 'Entrar - Gestão de Tarefas | MedicalThermo')

@section('content')
<div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-[#020a14] px-4 py-12">
    {{-- Deep engineering background --}}
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-950 via-[#0a1e35] to-[#04101e]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-white/[0.03]"></div>
        <div class="absolute inset-0 tech-grid opacity-[0.07]"></div>
        {{-- subtle blueprint lines --}}
        <div class="absolute inset-0 opacity-[0.04]" style="background-image: linear-gradient(rgba(67,165,219,0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(67,165,219,0.5) 1px, transparent 1px); background-size: 48px 48px;"></div>
    </div>

    {{-- Aurora blobs --}}
    <div class="pointer-events-none absolute -top-28 -left-28 size-[580px] rounded-full bg-gradient-to-br from-brand-400/20 via-brand-500/15 to-transparent blur-[70px] animate-float"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-24 size-[680px] rounded-full bg-gradient-to-tl from-brand-600/20 via-[#0a3a5c]/25 to-transparent blur-[80px] animate-float-reverse"></div>
    <div class="pointer-events-none absolute top-1/2 left-1/2 size-[760px] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gradient-to-br from-white/[0.05] via-transparent to-transparent blur-[50px] animate-aurora"></div>

    {{-- THEMATIC GHOST KANBAN — Gestão de Tarefas animada no fundo --}}
    <div class="pointer-events-none absolute inset-0 hidden lg:block overflow-hidden opacity-[0.12]">
        {{-- Ghost column 1 --}}
        <div class="absolute left-[6%] top-[18%] w-56 rounded-2xl border border-white/10 bg-white/[0.04] backdrop-blur-md p-3 animate-float" style="animation-delay: 0s">
            <div class="mb-3 flex items-center gap-2 text-[10px] font-semibold tracking-widest text-white/40 uppercase"><span class="size-1.5 rounded-full bg-amber-400"></span>Em andamento</div>
            <div class="space-y-2">
                <div class="rounded-xl border border-white/10 bg-white/[0.07] p-3"><div class="h-2 w-3/4 rounded bg-white/20"></div><div class="mt-2 h-1.5 w-1/2 rounded bg-white/10"></div></div>
                <div class="rounded-xl border border-white/10 bg-white/[0.07] p-3"><div class="h-2 w-2/3 rounded bg-white/20"></div><div class="mt-2 h-1.5 w-1/3 rounded bg-white/10"></div></div>
            </div>
        </div>
        {{-- Ghost column 2 --}}
        <div class="absolute right-[8%] top-[14%] w-56 rounded-2xl border border-white/10 bg-white/[0.04] backdrop-blur-md p-3 animate-float-reverse" style="animation-delay: 0.5s">
            <div class="mb-3 flex items-center gap-2 text-[10px] font-semibold tracking-widest text-white/40 uppercase"><span class="size-1.5 rounded-full bg-violet-400"></span>Aguardando aprovação</div>
            <div class="space-y-2">
                <div class="rounded-xl border border-white/10 bg-white/[0.07] p-3"><div class="h-2 w-5/6 rounded bg-white/20"></div><div class="mt-2 flex gap-1.5"><span class="h-4 w-14 rounded-full bg-violet-400/30"></span></div></div>
            </div>
        </div>
        {{-- Ghost column 3 --}}
        <div class="absolute left-[10%] bottom-[16%] w-56 rounded-2xl border border-white/10 bg-white/[0.04] backdrop-blur-md p-3 animate-float" style="animation-delay: 1s">
            <div class="mb-3 flex items-center gap-2 text-[10px] font-semibold tracking-widest text-white/40 uppercase"><span class="size-1.5 rounded-full bg-emerald-400"></span>Concluído</div>
            <div class="space-y-2">
                <div class="rounded-xl border border-white/10 bg-white/[0.07] p-3 opacity-60"><div class="h-2 w-3/4 rounded bg-white/15 line-through decoration-white/30"></div><div class="mt-2 h-1.5 w-1/2 rounded bg-white/10"></div></div>
            </div>
        </div>
        {{-- Ghost column 4 --}}
        <div class="absolute right-[12%] bottom-[18%] w-56 rounded-2xl border border-white/10 bg-white/[0.04] backdrop-blur-md p-3 animate-float-reverse" style="animation-delay: 1.5s">
            <div class="mb-3 flex items-center gap-2 text-[10px] font-semibold tracking-widest text-white/40 uppercase"><span class="size-1.5 rounded-full bg-brand-400"></span>Nova</div>
            <div class="space-y-2">
                <div class="rounded-xl border border-white/10 bg-white/[0.07] p-3"><div class="h-2 w-4/5 rounded bg-white/20"></div><div class="mt-2 h-1.5 w-2/5 rounded bg-white/10"></div></div>
            </div>
        </div>
        {{-- Connecting dashed path (gestão flow) --}}
        <svg class="absolute inset-0 size-full opacity-[0.08]" fill="none"><path d="M 180 200 C 380 260, 620 220, 980 180" stroke="white" stroke-width="1" stroke-dasharray="6 8" class="animate-dash" /></svg>
    </div>

    {{-- Content --}}
    <div class="relative w-full max-w-md">
        {{-- Logo with glow --}}
        <div class="mb-7 flex justify-center animate-fade-in">
            <div class="relative">
                <div class="pointer-events-none absolute -inset-8 rounded-full bg-brand-500/15 blur-2xl"></div>
                <img src="{{ asset('images/logo-medicalthermo-branco.png') }}" alt="MedicalThermo Engenharia" class="relative h-[52px] w-auto drop-shadow-[0_2px_20px_rgba(67,165,219,0.35)]">
            </div>
        </div>

        {{-- TRUE TRANSPARENT GLASS CARD --}}
        <div class="relative overflow-hidden rounded-[22px] border border-white/15 bg-white/[0.07] backdrop-blur-[20px] shadow-[0_20px_60px_rgba(0,0,0,0.45),inset_0_1px_0_rgba(255,255,255,0.18),inset_0_-1px_0_rgba(0,0,0,0.2)] animate-fade-in-up" style="animation-delay: 120ms">
            {{-- top shimmer line --}}
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
            <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-brand-400/50 to-transparent opacity-60"></div>

            <div class="p-7 sm:p-8">
                <div class="mb-6 text-center">
                    <h1 class="text-[22px] font-bold tracking-tight text-white drop-shadow-sm">Gestão de Tarefas</h1>
                    <p class="mt-1.5 text-[13px] leading-relaxed text-white/60">Entre com suas credenciais para continuar</p>
                    <div class="mx-auto mt-3 flex items-center justify-center gap-2">
                        <span class="h-px w-8 bg-gradient-to-r from-transparent to-white/15"></span>
                        <span class="size-1 rounded-full bg-white/20"></span>
                        <span class="h-px w-8 bg-gradient-to-l from-transparent to-white/15"></span>
                    </div>
                </div>

                @if(session('error'))
                    <div class="mb-5 rounded-xl border border-red-400/20 bg-red-500/15 backdrop-blur px-3.5 py-3 text-sm text-red-200">
                        <div class="flex gap-2.5"><svg class="size-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>{{ session('error') }}</span></div>
                    </div>
                @endif
                @if(session('success'))
                    <div class="mb-5 rounded-xl border border-emerald-400/20 bg-emerald-500/15 backdrop-blur px-3.5 py-3 text-sm text-emerald-200">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="mb-5 rounded-xl border border-red-400/20 bg-red-500/15 backdrop-blur px-3.5 py-3 text-sm text-red-200">
                        <ul class="list-disc pl-4 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div class="animate-fade-in-up" style="animation-delay: 180ms">
                        <label for="email" class="block text-[12.5px] font-semibold tracking-wide text-white/85">E-mail</label>
                        <div class="relative mt-1.5 group">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-white/35 group-focus-within:text-brand-300 transition-colors">
                                <svg class="size-[16px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus placeholder="seu@email.com"
                                class="block w-full rounded-xl border border-white/15 bg-white/[0.08] py-3 pl-10 pr-4 text-[14px] text-white placeholder-white/35 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] backdrop-blur outline-none transition-all focus:border-brand-400/50 focus:bg-white/[0.12] focus:ring-[3px] focus:ring-brand-500/20" />
                        </div>
                    </div>

                    <div class="animate-fade-in-up" style="animation-delay: 240ms">
                        <label for="password" class="block text-[12.5px] font-semibold tracking-wide text-white/85">Senha</label>
                        <div class="relative mt-1.5 group">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-white/35 group-focus-within:text-brand-300 transition-colors">
                                <svg class="size-[16px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15a3 3 0 100-6 3 3 0 000 6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                            </span>
                            <input type="password" name="password" id="password" required placeholder="Sua senha"
                                class="block w-full rounded-xl border border-white/15 bg-white/[0.08] py-3 pl-10 pr-4 text-[14px] text-white placeholder-white/35 shadow-[inset_0_1px_0_rgba(255,255,255,0.06)] backdrop-blur outline-none transition-all focus:border-brand-400/50 focus:bg-white/[0.12] focus:ring-[3px] focus:ring-brand-500/20" />
                        </div>
                    </div>

                    <button type="submit"
                        class="btn-shine group relative w-full overflow-hidden rounded-xl bg-gradient-to-br from-brand-500 via-brand-500 to-brand-600 px-4 py-3 text-[14px] font-semibold tracking-wide text-white shadow-[0_8px_24px_rgba(24,128,192,0.45),0_1px_0_rgba(255,255,255,0.2)_inset] outline-none transition-all hover:from-brand-400 hover:to-brand-600 hover:shadow-[0_12px_32px_rgba(24,128,192,0.55)] hover:-translate-y-[1px] active:translate-y-[0px] active:scale-[0.99] focus:ring-4 focus:ring-brand-500/25 animate-fade-in-up" style="animation-delay: 320ms">
                        <span class="relative flex items-center justify-center gap-2">
                            Entrar
                            <svg class="size-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </button>
                </form>

                <div class="mt-6 flex items-center gap-3">
                    <div class="h-px flex-1 bg-gradient-to-r from-transparent to-white/10"></div>
                    <span class="text-[10px] font-semibold tracking-[0.18em] text-white/35 uppercase">Acesso seguro</span>
                    <div class="h-px flex-1 bg-gradient-to-l from-transparent to-white/10"></div>
                </div>
            </div>

            {{-- bottom inner glow --}}
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"></div>
        </div>

        <p class="relative mt-6 flex items-center justify-center gap-2 text-center text-xs font-medium tracking-wide text-white/45 animate-fade-in" style="animation-delay: 520ms">
            <span class="size-1 rounded-full bg-white/20"></span>
            MedicalThermo Engenharia · Sistema interno de gestão de tarefas
            <span class="size-1 rounded-full bg-white/20"></span>
        </p>
    </div>

    {{-- animated checklist ticks at bottom --}}
    <div class="pointer-events-none absolute bottom-8 left-1/2 hidden -translate-x-1/2 items-center gap-6 text-white/25 lg:flex">
        <span class="flex items-center gap-1.5 text-xs"><span class="flex size-5 items-center justify-center rounded-full border border-white/15 bg-white/5 text-[10px] text-emerald-300">✓</span> Criou</span>
        <span class="h-3 w-px bg-white/10"></span>
        <span class="flex items-center gap-1.5 text-xs"><span class="flex size-5 items-center justify-center rounded-full border border-white/15 bg-white/5 text-[10px] text-amber-300">◷</span> Em andamento</span>
        <span class="h-3 w-px bg-white/10"></span>
        <span class="flex items-center gap-1.5 text-xs"><span class="flex size-5 items-center justify-center rounded-full border border-white/15 bg-white/5 text-[10px] text-violet-300">◎</span> Aprovação</span>
        <span class="h-3 w-px bg-white/10"></span>
        <span class="flex items-center gap-1.5 text-xs"><span class="flex size-5 items-center justify-center rounded-full border border-white/15 bg-white/5 text-[10px] text-emerald-300">✓</span> Concluído</span>
    </div>
</div>
@endsection
