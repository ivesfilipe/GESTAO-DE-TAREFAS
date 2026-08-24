@extends('layouts.app')

@section('auth', true)
@section('title', 'Definir sua senha - Gestão de Tarefas | MedicalThermo')

@section('content')
<div class="flex min-h-screen flex-col items-center justify-center px-4 py-12 bg-gradient-to-b from-brand-900 via-brand-800 to-brand-700">
    <div class="w-full max-w-md">
        <div class="mb-8 flex justify-center">
            <img src="{{ asset('images/logo-medicalthermo-branco.png') }}" alt="MedicalThermo Engenharia" class="h-14 w-auto">
        </div>

        <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8">
            <div class="mb-8 text-center">
                <h1 class="text-xl font-bold text-brand-900">Definir sua senha</h1>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $email ? 'Bem-vindo(a), ' . $email : 'Crie sua senha de acesso' }}
                </p>
            </div>

            @if(isset($error))
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    {{ $error }}
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

            <form method="POST" action="{{ route('invite.store', $token ?? '') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $token ?? '' }}">
                <input type="hidden" name="email" value="{{ $email ?? '' }}">

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Nova senha</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        required
                        minlength="8"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/25 outline-none"
                        placeholder="Mínimo 8 caracteres"
                    />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirmar senha</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        required
                        minlength="8"
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/25 outline-none"
                        placeholder="Repita a senha"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/50 transition-colors"
                >
                    Definir senha e acessar
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-brand-200">
            MedicalThermo Engenharia &middot; Sistema interno de gestão de tarefas
        </p>
    </div>
</div>
@endsection
