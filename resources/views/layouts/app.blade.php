<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50 dark:bg-[#020a14]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#083048">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gestão de Tarefas')</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon-192.png') }}">
    <link rel="manifest" href="/manifest.webmanifest">
    <script>
        (function(){try{var s=localStorage.getItem('mt-theme');var d=s? s==='dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;if(d){document.documentElement.classList.add('dark');var m=document.querySelector('meta[name="theme-color"]');if(m) m.setAttribute('content','#020a14');}}catch(e){}})();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="h-full bg-slate-50 dark:bg-[#020a14] text-slate-900 dark:text-slate-100 antialiased selection:bg-brand-100 selection:text-brand-900 transition-colors duration-300" @auth data-authenticated="true" data-user-id="{{ Auth::id() }}" data-reverb-key="{{ config('reverb.apps.apps.0.keys.key', config('reverb.apps.0.key')) }}" @endauth>
    @hasSection('auth')
        <div class="flex min-h-full flex-col justify-center bg-slate-50">
            <main class="flex-1">
                @yield('content')
            </main>
        </div>
    @else
        @auth
        <nav class="fixed top-0 z-40 w-full bg-white/80 dark:bg-slate-900/70 backdrop-blur-xl border-b border-slate-200/60 dark:border-white/10 h-16 lg:pl-64 supports-[backdrop-filter]:bg-white/70">
            <div class="flex h-full items-center justify-between px-4">
                <div class="flex items-center gap-3">
                    <a href="{{ route('tasks.index') }}" class="lg:hidden">
                        <img src="{{ asset('images/logo-medicalthermo.png') }}" alt="MedicalThermo Engenharia" class="h-7 w-auto dark:hidden">
                        <img src="{{ asset('images/logo-medicalthermo-branco.png') }}" alt="MedicalThermo Engenharia" class="hidden h-7 w-auto dark:block">
                    </a>
                    <span class="hidden lg:block text-lg font-semibold text-brand-900 dark:text-white">
                        Gestão de Tarefas
                        <span class="ml-1 text-xs font-normal text-slate-400 dark:text-slate-500">MedicalThermo Engenharia</span>
                    </span>
                </div>
                <div class="flex items-center gap-3 sm:gap-4">
                    {{-- Dark mode toggle --}}
                    <button type="button" data-theme-toggle aria-label="Alternar modo escuro" class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200/70 dark:border-white/10 bg-white/60 dark:bg-white/[0.06] backdrop-blur text-slate-500 dark:text-slate-400 hover:bg-white dark:hover:bg-white/10 hover:text-slate-700 dark:hover:text-white hover:shadow-sm transition-all" title="Modo escuro">
                        <span data-theme-icon class="relative size-5">
                            <svg data-icon="sun" class="absolute inset-0 size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <svg data-icon="moon" class="absolute inset-0 size-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                        </span>
                    </button>
                    <button type="button" id="palette-trigger" class="hidden sm:flex items-center gap-2 rounded-xl border border-slate-200/70 dark:border-white/10 bg-white/60 dark:bg-white/[0.06] backdrop-blur px-3 py-1.5 text-sm text-slate-400 dark:text-slate-400 hover:border-brand-200 dark:hover:border-white/20 hover:bg-white dark:hover:bg-white/10 hover:text-slate-600 dark:hover:text-white hover:shadow-sm transition-all" title="Buscar tarefas (Cmd+K)">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Buscar...
                        <kbd class="ml-2 rounded border border-slate-200 bg-white px-1.5 py-0.5 text-[10px] font-semibold">⌘K</kbd>
                    </button>
                    <a href="{{ route('notifications.index') }}" class="relative text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @php
                            $unreadCount = Auth::user()->unreadNotifications()->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span data-notification-badge class="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @else
                            <span data-notification-badge class="hidden absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"></span>
                        @endif
                    </a>
                    <span class="text-sm text-slate-700 dark:text-slate-200 hidden sm:block">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <aside class="fixed top-0 left-0 z-40 hidden h-full w-64 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-r border-slate-200/60 dark:border-white/10 lg:block supports-[backdrop-filter]:bg-white/70">
            <div class="flex h-16 items-center justify-between border-b border-slate-200 dark:border-white/10 px-6">
                <a href="{{ Auth::user()->isGestor() ? url('/painel') : url('/minhas-tarefas') }}">
                    <img src="{{ asset('images/logo-medicalthermo.png') }}" alt="MedicalThermo Engenharia" class="h-9 w-auto dark:hidden">
                    <img src="{{ asset('images/logo-medicalthermo-branco.png') }}" alt="MedicalThermo Engenharia" class="hidden h-9 w-auto dark:block">
                </a>
                <button type="button" data-theme-toggle class="hidden lg:inline-flex size-8 items-center justify-center rounded-lg border border-slate-200/60 dark:border-white/10 bg-white/50 dark:bg-white/[0.06] text-slate-500 dark:text-slate-400 hover:bg-white dark:hover:bg-white/10 transition-colors" title="Modo escuro">
                    <span data-theme-icon class="relative size-4">
                        <svg data-icon="sun" class="absolute inset-0 size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        <svg data-icon="moon" class="absolute inset-0 size-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                    </span>
                </button>
            </div>
            <div class="px-6 pt-4 pb-1">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Gestão de Tarefas</span>
            </div>
            <nav class="mt-4 px-3 space-y-1">
                @if(Auth::user()->isGestor())
                    <a href="{{ url('/painel') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('painel') ? 'bg-gradient-to-br from-brand-50 to-brand-100/70 dark:from-brand-500/15 dark:to-brand-600/15 text-brand-700 dark:text-brand-300 shadow-sm border border-brand-200/50 dark:border-brand-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-white/[0.06] hover:shadow-sm hover:border hover:border-slate-200/60 dark:hover:border-white/10 hover:translate-x-0.5 border border-transparent dark:border-transparent hover:text-slate-700 dark:hover:text-white' }}">
                        <svg class="size-5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        Painel
                    </a>
                    <a href="{{ route('tasks.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('tarefas') ? 'bg-gradient-to-br from-brand-50 to-brand-100/70 dark:from-brand-500/15 dark:to-brand-600/15 text-brand-700 dark:text-brand-300 shadow-sm border border-brand-200/50 dark:border-brand-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-white/[0.06] hover:shadow-sm hover:border hover:border-slate-200/60 dark:hover:border-white/10 hover:translate-x-0.5 border border-transparent dark:border-transparent hover:text-slate-700 dark:hover:text-white' }}">
                        <svg class="size-5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Tarefas
                    </a>
                    <a href="{{ route('tasks.kanban') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('tarefas/quadro') ? 'bg-gradient-to-br from-brand-50 to-brand-100/70 dark:from-brand-500/15 dark:to-brand-600/15 text-brand-700 dark:text-brand-300 shadow-sm border border-brand-200/50 dark:border-brand-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-white/[0.06] hover:shadow-sm hover:border hover:border-slate-200/60 dark:hover:border-white/10 hover:translate-x-0.5 border border-transparent dark:border-transparent hover:text-slate-700 dark:hover:text-white' }}">
                        <svg class="size-5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm9 0a1 1 0 011-1h4a1 1 0 011 1v9a1 1 0 01-1 1h-4a1 1 0 01-1-1V5z"/>
                        </svg>
                        Quadro
                    </a>
                    <a href="{{ route('team.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('equipe*') ? 'bg-gradient-to-br from-brand-50 to-brand-100/70 dark:from-brand-500/15 dark:to-brand-600/15 text-brand-700 dark:text-brand-300 shadow-sm border border-brand-200/50 dark:border-brand-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-white/[0.06] hover:shadow-sm hover:border hover:border-slate-200/60 dark:hover:border-white/10 hover:translate-x-0.5 border border-transparent dark:border-transparent hover:text-slate-700 dark:hover:text-white' }}">
                        <svg class="size-5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Equipe
                    </a>
                    <a href="{{ route('reports.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('relatorios*') ? 'bg-gradient-to-br from-brand-50 to-brand-100/70 dark:from-brand-500/15 dark:to-brand-600/15 text-brand-700 dark:text-brand-300 shadow-sm border border-brand-200/50 dark:border-brand-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-white/[0.06] hover:shadow-sm hover:border hover:border-slate-200/60 dark:hover:border-white/10 hover:translate-x-0.5 border border-transparent dark:border-transparent hover:text-slate-700 dark:hover:text-white' }}">
                        <svg class="size-5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Relatórios
                    </a>
                    <a href="/pulse" target="_blank" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('pulse*') ? 'bg-gradient-to-br from-brand-50 to-brand-100/70 dark:from-brand-500/15 dark:to-brand-600/15 text-brand-700 dark:text-brand-300 shadow-sm border border-brand-200/50 dark:border-brand-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-white/[0.06] hover:shadow-sm hover:border hover:border-slate-200/60 dark:hover:border-white/10 hover:translate-x-0.5 border border-transparent dark:border-transparent hover:text-slate-700 dark:hover:text-white' }}">
                        <svg class="size-5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Monitoramento
                    </a>
                    <a href="{{ route('assistant.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('assistente*') ? 'bg-gradient-to-br from-brand-50 to-brand-100/70 dark:from-brand-500/15 dark:to-brand-600/15 text-brand-700 dark:text-brand-300 shadow-sm border border-brand-200/50 dark:border-brand-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-white/[0.06] hover:shadow-sm hover:border hover:border-slate-200/60 dark:hover:border-white/10 hover:translate-x-0.5 border border-transparent dark:border-transparent hover:text-slate-700 dark:hover:text-white' }}">
                        <svg class="size-5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/>
                        </svg>
                        Assistente
                    </a>
                    <a href="{{ route('schedule.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('agenda-inteligente*') ? 'bg-gradient-to-br from-brand-50 to-brand-100/70 dark:from-brand-500/15 dark:to-brand-600/15 text-brand-700 dark:text-brand-300 shadow-sm border border-brand-200/50 dark:border-brand-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-white/[0.06] hover:shadow-sm hover:border hover:border-slate-200/60 dark:hover:border-white/10 hover:translate-x-0.5 border border-transparent dark:border-transparent hover:text-slate-700 dark:hover:text-white' }}">
                        <svg class="size-5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Agenda Inteligente
                    </a>
                @else
                    <a href="{{ url('/minhas-tarefas') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('minhas-tarefas*') ? 'bg-gradient-to-br from-brand-50 to-brand-100/70 dark:from-brand-500/15 dark:to-brand-600/15 text-brand-700 dark:text-brand-300 shadow-sm border border-brand-200/50 dark:border-brand-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-white/[0.06] hover:shadow-sm hover:border hover:border-slate-200/60 dark:hover:border-white/10 hover:translate-x-0.5 border border-transparent dark:border-transparent hover:text-slate-700 dark:hover:text-white' }}">
                        <svg class="size-5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Minhas Tarefas
                    </a>
                @endif
                <a href="{{ route('notifications.index') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->is('notificacoes*') ? 'bg-gradient-to-br from-brand-50 to-brand-100/70 dark:from-brand-500/15 dark:to-brand-600/15 text-brand-700 dark:text-brand-300 shadow-sm border border-brand-200/50 dark:border-brand-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-white dark:hover:bg-white/[0.06] hover:shadow-sm hover:border hover:border-slate-200/60 dark:hover:border-white/10 hover:translate-x-0.5 border border-transparent dark:border-transparent hover:text-slate-700 dark:hover:text-white' }}">
                    <svg class="size-5 transition-transform group-hover:scale-105" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notificações
                </a>
            </nav>
        </aside>

        <nav class="fixed bottom-0 left-0 z-40 w-full border-t border-slate-200/60 bg-white/85 dark:bg-slate-900/80 backdrop-blur-xl supports-[backdrop-filter]:bg-white/75 lg:hidden dark:border-white/10">
            <div class="flex h-16 items-center justify-around">
                @if(Auth::user()->isGestor())
                    <a href="{{ url('/painel') }}" class="flex flex-col items-center gap-1 {{ request()->is('painel') ? 'text-brand-700' : 'text-slate-500' }}">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span class="text-[10px] font-medium">Painel</span>
                    </a>
                    <a href="{{ route('tasks.index') }}" class="flex flex-col items-center gap-1 {{ request()->is('tarefas*') ? 'text-brand-700' : 'text-slate-500' }}">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="text-[10px] font-medium">Tarefas</span>
                    </a>
                    <a href="{{ route('team.index') }}" class="flex flex-col items-center gap-1 {{ request()->is('equipe*') ? 'text-brand-700' : 'text-slate-500' }}">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="text-[10px] font-medium">Equipe</span>
                    </a>
                @else
                    <a href="{{ url('/minhas-tarefas') }}" class="flex flex-col items-center gap-1 {{ request()->is('minhas-tarefas*') ? 'text-brand-700' : 'text-slate-500' }}">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="text-[10px] font-medium">Tarefas</span>
                    </a>
                @endif
                <a href="{{ route('notifications.index') }}" class="flex flex-col items-center gap-1 {{ request()->is('notificacoes*') ? 'text-brand-700' : 'text-slate-500' }}">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="text-[10px] font-medium">Notificações</span>
                </a>
            </div>
        </nav>
        @endauth

        <main class="relative pt-16 pb-20 lg:pl-64 min-h-screen bg-slate-50 dark:bg-[#020a14] transition-colors duration-300">
            {{-- page aurora — more colorful like login --}}
            <div class="pointer-events-none absolute inset-0 overflow-hidden">
                <div class="absolute -top-32 -right-32 size-[560px] rounded-full bg-gradient-to-br from-brand-100/40 via-brand-50/20 dark:from-brand-600/12 dark:via-brand-800/8 to-transparent blur-3xl"></div>
                <div class="absolute top-96 -left-32 size-[480px] rounded-full bg-gradient-to-br from-slate-100/60 dark:from-slate-800/20 to-transparent blur-3xl"></div>
                <div class="absolute top-[40%] right-[15%] hidden dark:block size-[640px] rounded-full bg-gradient-to-br from-violet-600/8 via-transparent to-transparent blur-[70px]"></div>
                <div class="absolute bottom-0 left-[20%] hidden dark:block size-[560px] rounded-full bg-gradient-to-t from-brand-600/8 via-transparent to-transparent blur-[70px]"></div>
                <div class="absolute inset-0 hidden dark:block opacity-[0.03]" style="background-image: linear-gradient(rgba(255,255,255,0.4) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 32px 32px;"></div>
            </div>
            <div class="relative">
            @if(session('success'))
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700">
                        {{ session('success') }}
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                </div>
            @endif
            @if($errors->any())
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-4">
                    <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
            </div>
        </main>
    @endif

    @livewireScripts
    @stack('scripts')

    @auth
    <div id="command-palette" class="hidden fixed inset-0 z-[90] bg-slate-900/45 backdrop-blur-sm p-4 pt-[15vh]">
        <div class="mx-auto w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200/60 bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl shadow-2xl animate-scale-in dark:border-white/10">
            <div class="border-b border-slate-100">
                <input id="palette-input" type="text" placeholder="Buscar tarefas por título ou descrição..." autocomplete="off"
                       class="w-full bg-transparent px-4 py-3.5 text-sm text-slate-800 outline-none placeholder:text-slate-400"/>
            </div>
            <div id="palette-results" class="max-h-80 divide-y divide-slate-50 overflow-y-auto py-1"></div>
            <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-4 py-2 text-[11px] text-slate-400">
                <span>↑↓ navegar · ↵ abrir · esc fechar</span>
                <span>Busca global</span>
            </div>
        </div>
    </div>
    @endauth
</body>
</html>
