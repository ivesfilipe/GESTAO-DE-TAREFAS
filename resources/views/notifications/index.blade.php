@extends('layouts.app')

@section('title', 'Notificações - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Notificações</h1>

    <div class="space-y-2">
        @forelse($notifications as $notification)
            <div class="rounded-xl bg-white border border-slate-200 p-4 shadow-sm {{ is_null($notification->read_at) ? 'border-l-4 border-l-blue-500 bg-blue-50/30' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        @if(is_null($notification->read_at))
                            <span class="mt-1.5 flex size-2 shrink-0 rounded-full bg-blue-500"></span>
                        @else
                            <span class="mt-1.5 flex size-2 shrink-0 rounded-full bg-slate-300"></span>
                        @endif
                        <div>
                            <p class="text-sm text-slate-700">{{ $notification->data['message'] ?? 'Notificação' }}</p>
                            <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if(is_null($notification->read_at))
                        <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="shrink-0 text-xs font-medium text-blue-600 hover:text-blue-700">
                                Marcar como lida
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-white border border-slate-200 p-12 text-center shadow-sm">
                <svg class="mx-auto size-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="mt-3 text-sm font-medium text-slate-900">Nenhuma notificação</h3>
                <p class="mt-1 text-sm text-slate-500">Você está em dia com tudo.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
