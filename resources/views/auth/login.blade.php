@extends('layouts.app')

@section('auth', true)
@section('title', 'Entrar - Gestão de Tarefas')

@section('content')
<div class="flex min-h-full items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-blue-800">Gestão de Tarefas</h1>
                <p class="mt-2 text-sm text-slate-500">Entre com suas credenciais</p>
            </div>

            @if($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">E-mail</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none"
                        placeholder="seu@email.com"
                    />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Senha</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none"
                        placeholder="Sua senha"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-blue-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-colors"
                >
                    Entrar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
