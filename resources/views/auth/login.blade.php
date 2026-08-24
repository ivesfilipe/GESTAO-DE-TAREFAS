@extends('layouts.app')

@section('auth', true)
@section('title', 'Entrar - Gestão de Tarefas | MedicalThermo')

@section('content')
<div class="flex min-h-screen flex-col items-center justify-center px-4 py-12 bg-gradient-to-b from-brand-900 via-brand-800 to-brand-700">
    <div class="w-full max-w-md">
        <div class="mb-8 flex justify-center">
            <img src="{{ asset('images/logo-medicalthermo-branco.png') }}" alt="MedicalThermo Engenharia" class="h-14 w-auto">
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8">
            <div class="mb-8 text-center">
                <h1 class="text-xl font-bold text-brand-900">Gestão de Tarefas</h1>
                <p class="mt-1 text-sm text-slate-500">Entre com suas credenciais para continuar</p>
            </div>

            @if(session('error'))
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

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
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/25 outline-none"
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
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/25 outline-none"
                        placeholder="Sua senha"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-colors"
                >
                    Entrar
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-brand-200">
            MedicalThermo Engenharia &middot; Sistema interno de gestão de tarefas
        </p>
    </div>
</div>
@endsection
