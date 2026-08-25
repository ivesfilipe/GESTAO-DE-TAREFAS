@extends('layouts.app')

@section('title', 'Relatórios - Gestão de Tarefas')

@section('content')
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl font-bold text-slate-900">Relatórios de Desempenho</h1>
        <div class="flex items-center gap-1 rounded-lg bg-white border border-slate-200 p-1 shadow-sm">
            @foreach([30 => '30 dias', 90 => '90 dias', 365 => '12 meses'] as $value => $label)
                <a href="{{ route('reports.index', ['periodo' => $value]) }}"
                   class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors {{ $days === $value ? 'bg-brand-700 text-white' : 'text-slate-600 hover:bg-slate-100' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Criadas no período</p>
            <p class="mt-1 text-3xl font-bold text-slate-900">{{ $createdCount }}</p>
        </div>
        <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Concluídas (aprovadas)</p>
            <p class="mt-1 text-3xl font-bold text-green-600">{{ $approvedCount }}</p>
        </div>
        <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Tempo médio de conclusão</p>
            <p class="mt-1 text-3xl font-bold text-brand-700">
                {{ $avgCycleDays !== null ? number_format($avgCycleDays, 1, ',') . ' dias' : '—' }}
            </p>
        </div>
        <div class="rounded-xl bg-white border border-slate-200 p-5 shadow-sm">
            <p class="text-sm text-slate-500">Entregas fora do prazo</p>
            <p class="mt-1 text-3xl font-bold {{ $lateRate > 25 ? 'text-red-600' : 'text-slate-900' }}">
                {{ number_format($lateRate) }}%
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 rounded-xl bg-white border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-sm font-semibold text-slate-900">Desempenho por pessoa</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-left text-slate-500">
                            <th class="px-6 py-3 font-medium">Liderado</th>
                            <th class="px-4 py-3 font-medium text-center">Abertas</th>
                            <th class="px-4 py-3 font-medium text-center">Atrasadas</th>
                            <th class="px-4 py-3 font-medium text-center">Concluídas</th>
                            <th class="px-4 py-3 font-medium text-center">Ciclo médio</th>
                            <th class="px-4 py-3 font-medium text-center">Fora do prazo</th>
                            <th class="px-6 py-3 font-medium text-center">Reprovadas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($perUser as $row)
                            <tr class="hover:bg-slate-50/50">
                                <td class="px-6 py-4 font-medium text-slate-900">{{ $row->user->name }}</td>
                                <td class="px-4 py-4 text-center text-slate-600">{{ $row->open }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span class="{{ $row->overdue > 0 ? 'text-red-600 font-semibold' : 'text-slate-400' }}">{{ $row->overdue }}</span>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-16 h-2 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-full rounded-full bg-brand-500" style="width: {{ $row->concluded / $maxConcluded * 100 }}%"></div>
                                        </div>
                                        <span class="font-semibold text-slate-900">{{ $row->concluded }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center text-slate-600">
                                    {{ $row->avg_cycle_days !== null ? number_format($row->avg_cycle_days, 1, ',') . ' d' : '—' }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="{{ $row->late_rate > 25 ? 'text-red-600 font-semibold' : 'text-slate-600' }}">{{ $row->late_rate }}%</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="{{ $row->rejected > 0 ? 'text-orange-600 font-semibold' : 'text-slate-400' }}">{{ $row->rejected }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 italic">Nenhum liderado ativo.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl bg-white border border-slate-200 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-1">Reprovações por motivo</h2>
            <p class="text-xs text-slate-400 mb-4">{{ $rejectedCount }} no período</p>

            @forelse($categoryLabels as $category => $label)
                @php($total = $rejectionCategories[$category] ?? 0)
                <div class="mb-3">
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-slate-600">{{ $label }}</span>
                        <span class="font-semibold text-slate-900">{{ $total }}</span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full {{ $total > 0 ? 'bg-red-400' : '' }}"
                             style="width: {{ $rejectedCount > 0 && $total > 0 ? $total / $rejectedCount * 100 : 0 }}%"></div>
                    </div>
                </div>
            @endforeach

            <div class="mt-6 rounded-lg bg-brand-50 border border-brand-200 p-4 text-sm text-brand-800">
                <span class="font-semibold">Taxa de retrabalho:</span>
                {{ number_format($rejectionRate, 1, ',') }}%
                <span class="text-brand-600">(reprovadas ÷ revisadas)</span>
            </div>
        </div>
    </div>
</div>
@endsection
