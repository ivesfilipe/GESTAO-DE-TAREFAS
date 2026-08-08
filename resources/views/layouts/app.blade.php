<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Gestão de Tarefas')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-slate-50 text-slate-900">
    @hasSection('auth')
        <div class="flex min-h-full flex-col justify-center bg-slate-50">
            <main class="flex-1">
                @yield('content')
            </main>
        </div>
    @else
        @auth
        <nav class="fixed top-0 z-40 w-full bg-white border-b border-slate-200 h-16 lg:pl-64">
            <div class="flex h-full items-center justify-between px-4">
                <div class="flex items-center gap-3">
                    <span class="text-lg font-semibold text-blue-800">Gestão de Tarefas</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('notifications.index') }}" class="relative text-slate-500 hover:text-slate-700">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @php
                            $unreadCount = Auth::user()->unreadNotifications()->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </a>
                    <span class="text-sm text-slate-700 hidden sm:block">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-slate-500 hover:text-red-600 transition-colors">
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <aside class="fixed top-0 left-0 z-40 hidden h-full w-64 bg-white border-r border-slate-200 lg:block">
            <div class="flex h-16 items-center border-b border-slate-200 px-6">
                <a href="{{ Auth::user()->isGestor() ? url('/painel') : url('/minhas-tarefas') }}" class="text-lg font-semibold text-blue-800">
                    Gestão de Tarefas
                </a>
            </div>
            <nav class="mt-4 px-3">
                @if(Auth::user()->isGestor())
                    <a href="{{ url('/painel') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->is('painel') ? 'bg-blue-50 text-blue-800' : 'text-slate-600 hover:bg-slate-100' }}">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        Painel
                    </a>
                    <a href="{{ route('tasks.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->is('tarefas*') ? 'bg-blue-50 text-blue-800' : 'text-slate-600 hover:bg-slate-100' }}">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Tarefas
                    </a>
                    <a href="{{ route('team.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->is('equipe*') ? 'bg-blue-50 text-blue-800' : 'text-slate-600 hover:bg-slate-100' }}">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Equipe
                    </a>
                @else
                    <a href="{{ url('/minhas-tarefas') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->is('minhas-tarefas*') ? 'bg-blue-50 text-blue-800' : 'text-slate-600 hover:bg-slate-100' }}">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Minhas Tarefas
                    </a>
                @endif
                <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->is('notificacoes*') ? 'bg-blue-50 text-blue-800' : 'text-slate-600 hover:bg-slate-100' }}">
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notificações
                </a>
            </nav>
        </aside>

        <nav class="fixed bottom-0 left-0 z-40 w-full border-t border-slate-200 bg-white lg:hidden">
            <div class="flex h-16 items-center justify-around">
                @if(Auth::user()->isGestor())
                    <a href="{{ url('/painel') }}" class="flex flex-col items-center gap-1 {{ request()->is('painel') ? 'text-blue-800' : 'text-slate-500' }}">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span class="text-[10px] font-medium">Painel</span>
                    </a>
                    <a href="{{ route('tasks.index') }}" class="flex flex-col items-center gap-1 {{ request()->is('tarefas*') ? 'text-blue-800' : 'text-slate-500' }}">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="text-[10px] font-medium">Tarefas</span>
                    </a>
                    <a href="{{ route('team.index') }}" class="flex flex-col items-center gap-1 {{ request()->is('equipe*') ? 'text-blue-800' : 'text-slate-500' }}">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="text-[10px] font-medium">Equipe</span>
                    </a>
                @else
                    <a href="{{ url('/minhas-tarefas') }}" class="flex flex-col items-center gap-1 {{ request()->is('minhas-tarefas*') ? 'text-blue-800' : 'text-slate-500' }}">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span class="text-[10px] font-medium">Tarefas</span>
                    </a>
                @endif
                <a href="{{ route('notifications.index') }}" class="flex flex-col items-center gap-1 {{ request()->is('notificacoes*') ? 'text-blue-800' : 'text-slate-500' }}">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="text-[10px] font-medium">Notificações</span>
                </a>
            </div>
        </nav>
        @endauth

        <main class="pt-16 pb-20 lg:pl-64">
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

            @yield('content')
        </main>
    @endif

    @stack('scripts')
</body>
</html>
