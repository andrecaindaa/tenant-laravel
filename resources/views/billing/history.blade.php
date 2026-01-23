@extends('layouts.app')

@section('title', 'Histórico de Billing')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Histórico de Alterações de Plano</h1>

    <!-- Stats Summary -->
    <div class="grid md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl border border-gray-200">
            <div class="text-2xl font-bold text-blue-600">{{ $logs->total() }}</div>
            <div class="text-gray-500">Total de Alterações</div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-gray-200">
            <div class="text-2xl font-bold text-green-600">
                {{ $logs->where('action', 'upgrade')->count() }}
            </div>
            <div class="text-gray-500">Upgrades</div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-gray-200">
            <div class="text-2xl font-bold text-amber-600">
                {{ $logs->whereIn('action', ['downgrade_scheduled', 'downgrade_applied'])->count() }}
            </div>
            <div class="text-gray-500">Downgrades</div>
        </div>
        <div class="bg-white p-6 rounded-xl border border-gray-200">
            <div class="text-2xl font-bold text-purple-600">
                {{ $logs->where('action', 'like', '%trial%')->count() }}
            </div>
            <div class="text-gray-500">Trial</div>
        </div>
    </div>

    <!-- History Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">De/Para</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Detalhes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizador</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $log->created_at->format('d/m/Y') }}</div>
                                <div class="text-sm text-gray-500">{{ $log->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $badgeColors = [
                                        'upgrade' => 'bg-green-100 text-green-800',
                                        'downgrade_scheduled' => 'bg-amber-100 text-amber-800',
                                        'downgrade_applied' => 'bg-red-100 text-red-800',
                                        'downgrade_cancelled' => 'bg-gray-100 text-gray-800',
                                        'trial_started' => 'bg-blue-100 text-blue-800',
                                        'trial_ending_notification' => 'bg-purple-100 text-purple-800',
                                        'trial_expired' => 'bg-red-100 text-red-800',
                                        'trial_expired_downgrade' => 'bg-red-100 text-red-800',
                                    ];

                                    $labels = [
                                        'upgrade' => '⬆️ Upgrade',
                                        'downgrade_scheduled' => '⏰ Downgrade Agendado',
                                        'downgrade_applied' => '🔁 Downgrade Aplicado',
                                        'downgrade_cancelled' => '❌ Downgrade Cancelado',
                                        'trial_started' => '🎯 Trial Iniciado',
                                        'trial_ending_notification' => '📧 Notificação Trial',
                                        'trial_expired' => '⏰ Trial Expirado',
                                        'trial_expired_downgrade' => '⬇️ Downgrade por Trial',
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $badgeColors[$log->action] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $labels[$log->action] ?? $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(isset($log->metadata['from_plan_id']) && isset($log->metadata['to_plan_id']))
                                    @php
                                        $fromPlan = \App\Models\Plan::find($log->metadata['from_plan_id']);
                                        $toPlan = \App\Models\Plan::find($log->metadata['to_plan_id']);
                                    @endphp
                                    <div class="text-sm">
                                        <span class="font-medium">{{ $fromPlan->name ?? 'N/A' }}</span>
                                        <span class="text-gray-500 mx-2">→</span>
                                        <span class="font-medium">{{ $toPlan->name ?? 'N/A' }}</span>
                                    </div>
                                @elseif(isset($log->metadata['days_left']))
                                    <div class="text-sm text-gray-600">
                                        {{ $log->metadata['days_left'] }} dias restantes
                                    </div>
                                @else
                                    <div class="text-sm text-gray-400">—</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    @if(isset($log->metadata['scheduled_for']))
                                        Agendado para: {{ \Carbon\Carbon::parse($log->metadata['scheduled_for'])->format('d/m/Y') }}
                                    @elseif(isset($log->metadata['automated']))
                                        <span class="text-gray-500">Automático</span>
                                    @elseif(isset($log->metadata['prorated']))
                                        <span class="text-green-500">Pró-rata aplicado</span>
                                    @endif
                                </div>
                                @if(isset($log->metadata['notes']))
                                    <div class="text-sm text-gray-500 mt-1">{{ $log->metadata['notes'] }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->user)
                                    <div class="text-sm text-gray-900">{{ $log->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $log->user->email }}</div>
                                @else
                                    <div class="text-sm text-gray-400">Sistema</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <div class="text-gray-400 mb-2">📝</div>
                                Nenhum registo encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Back Link -->
    <div class="mt-8">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar ao Dashboard
        </a>
    </div>
</div>
@endsection
