@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600 mt-2">Bem-vindo ao seu workspace</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-4">
            @if($tenant->onTrial())
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    <i class="fas fa-clock mr-2"></i>
                    Trial: {{ $trialDaysLeft }} dias restantes
                </span>
            @endif
            <span class="badge {{ $plan && $plan->isFree() ? 'bg-gray-200' : 'bg-blue-200' }}">
            {{ $plan->name ?? 'Free' }}
            </span>
        </div>
    </div>

    <!-- Alertas -->
    @if($tenant->onTrial() && $trialDaysLeft <= 3)
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Trial a terminar!
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>
                            O seu trial termina em <strong>{{ $trialDaysLeft }} dias</strong>
                            ({{ $trialEndsAt->format('d/m/Y') }}).
                            <a href="{{ route('billing.plans') }}" class="underline ml-1">Escolha um plano</a> para continuar.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($subscription && $subscription->hasScheduledDowngrade())
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-calendar-alt text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">
                        Downgrade agendado
                    </h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>
                            O downgrade para <strong>{{ $subscription->nextPlan->name ?? 'Free' }}</strong>
                            está agendado para <strong>{{ $subscription->next_plan_starts_at->format('d/m/Y') }}</strong>.
                        </p>
                        <form method="POST" action="{{ route('billing.cancel-downgrade') }}" class="mt-2">
                            @csrf
                            <button type="submit" class="text-sm bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded">
                                Cancelar downgrade
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Grid de Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Tenant Info -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Tenant</h3>
                <div class="p-2 bg-gray-100 rounded-lg">
                    <i class="fas fa-building text-gray-600"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-2">{{ $tenant->name }}</p>
            <p class="text-gray-600 text-sm">{{ $tenant->slug }}</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-500">
                    <i class="far fa-calendar mr-2"></i>
                    Criado em {{ $tenant->created_at->format('d/m/Y') }}
                </p>
                <p class="text-sm text-gray-500 mt-1">
                    <i class="fas fa-users mr-2"></i>
                    {{ $usage['users'] ?? 0 }} utilizadores
                </p>
            </div>
        </div>

        <!-- Plano Atual -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Plano Atual</h3>
                <div class="p-2 {{ $plan && $plan->isFree() ? 'bg-gray-100' : 'bg-blue-100' }} rounded-lg">
                    <i class="fas fa-crown {{ $plan && $plan->isFree() ? 'text-gray-600' : 'text-blue-600' }}"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-2">{{ $plan->name ?? 'Free' }}</p>
            <p class="text-gray-600 text-sm">
    @if($plan && $plan->price > 0)
        {{ number_format($plan->price, 2) }}€
        @if($plan->interval === 'yearly')
            /ano
        @else
            /mês
        @endif
    @else
        Grátis
    @endif
</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="{{ route('billing.plans') }}"
                   class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                    Alterar plano
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>

        <!-- Status do Trial -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Trial</h3>
                <div class="p-2 {{ $onTrial ? 'bg-yellow-100' : 'bg-gray-100' }} rounded-lg">
                    <i class="fas fa-clock {{ $onTrial ? 'text-yellow-600' : 'text-gray-600' }}"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-2">
                {{ $onTrial ? 'Ativo' : 'Inativo' }}
            </p>
            <p class="text-gray-600 text-sm">
                @if($onTrial)
                    Termina em {{ $trialDaysLeft }} dias
                @else
                    Não está em trial
                @endif
            </p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-500">
                    <i class="far fa-calendar-alt mr-2"></i>
                    {{ $trialEndsAt ? $trialEndsAt->format('d/m/Y') : 'N/A' }}
                </p>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Ações</h3>
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-bolt text-green-600"></i>
                </div>
            </div>
            <div class="space-y-3">
                <a href="{{ route('tenants.create') }}"
                   class="flex items-center text-gray-700 hover:text-gray-900 hover:bg-gray-50 p-2 rounded">
                    <i class="fas fa-plus-circle text-green-500 mr-3"></i>
                    <span>Criar novo tenant</span>
                </a>
                <a href="{{ route('billing.plans') }}"
                   class="flex items-center text-gray-700 hover:text-gray-900 hover:bg-gray-50 p-2 rounded">
                    <i class="fas fa-chart-line text-blue-500 mr-3"></i>
                    <span>Ver planos</span>
                </a>
                <a href="{{ route('billing.history') }}"
                   class="flex items-center text-gray-700 hover:text-gray-900 hover:bg-gray-50 p-2 rounded">
                    <i class="fas fa-history text-purple-500 mr-3"></i>
                    <span>Histórico</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Limites de Uso -->
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Limites de Uso</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($limits as $key => $limit)
                @php
                    $current = $usage[$key] ?? 0;
                    $percentage = $limit > 0 ? min(100, ($current / $limit) * 100) : 0;
                    $isLimitReached = $reachedLimits[$key] ?? false;
                    $color = $isLimitReached ? 'red' : ($percentage > 80 ? 'yellow' : 'green');
                    $colors = [
                        'red' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'progress' => 'bg-red-500'],
                        'yellow' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'progress' => 'bg-yellow-500'],
                        'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'progress' => 'bg-green-500'],
                    ];
                @endphp

                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center">
                            <div class="p-2 {{ $colors[$color]['bg'] }} rounded-lg mr-3">
                                @if($key === 'users')
                                    <i class="fas fa-users {{ $colors[$color]['text'] }}"></i>
                                @elseif($key === 'projects')
                                    <i class="fas fa-folder {{ $colors[$color]['text'] }}"></i>
                                @else
                                    <i class="fas fa-chart-bar {{ $colors[$color]['text'] }}"></i>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">{{ ucfirst($key) }}</h4>
                                <p class="text-sm text-gray-500">
                                    {{ $current }} / {{ $limit ?: 'Ilimitado' }}
                                </p>
                            </div>
                        </div>
                        <span class="text-sm font-medium {{ $colors[$color]['text'] }}">
                            {{ $percentage }}%
                        </span>
                    </div>

                    @if($limit)
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full {{ $colors[$color]['progress'] }} rounded-full"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    @endif

                    @if($isLimitReached)
                        <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded">
                            <p class="text-sm text-red-700">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Limite atingido!
                                <a href="{{ route('billing.plans') }}" class="underline font-medium">Upgrade seu plano</a>
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Atividade Recente -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-900">Atividade Recente</h2>
            <a href="{{ route('billing.history') }}" class="text-sm text-blue-600 hover:text-blue-800">
                Ver tudo
            </a>
        </div>

        <div class="space-y-4">
            @forelse($recentLogs as $log)
                <div class="flex items-start p-3 hover:bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0 mr-4">
                        @switch($log->action)
                            @case('upgrade')
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-arrow-up text-green-600"></i>
                                </div>
                                @break
                            @case('downgrade_scheduled')
                                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-yellow-600"></i>
                                </div>
                                @break
                            @case('downgrade_applied')
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-arrow-down text-red-600"></i>
                                </div>
                                @break
                            @case('trial_started')
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-clock text-blue-600"></i>
                                </div>
                                @break
                            @default
                                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-history text-gray-600"></i>
                                </div>
                        @endswitch
                    </div>

                    <div class="flex-1">
                        <div class="flex justify-between">
                            <h4 class="font-medium text-gray-900">
                                @switch($log->action)
                                    @case('upgrade') Upgrade de plano @break
                                    @case('downgrade_scheduled') Downgrade agendado @break
                                    @case('downgrade_applied') Downgrade aplicado @break
                                    @case('trial_started') Trial iniciado @break
                                    @case('trial_ending_notification') Notificação de trial @break
                                    @case('trial_expired') Trial expirado @break
                                    @default Alteração de plano
                                @endswitch
                            </h4>
                            <span class="text-sm text-gray-500">
                                {{ $log->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="text-gray-600 text-sm mt-1">
                            @if(isset($log->metadata['from_plan_id']) && isset($log->metadata['to_plan_id']))
                                @php
                                    $fromPlan = \App\Models\Plan::find($log->metadata['from_plan_id']);
                                    $toPlan = \App\Models\Plan::find($log->metadata['to_plan_id']);
                                @endphp
                                De <strong>{{ $fromPlan->name ?? 'N/A' }}</strong>
                                para <strong>{{ $toPlan->name ?? 'N/A' }}</strong>
                            @elseif(isset($log->metadata['days_left']))
                                {{ $log->metadata['days_left'] }} dias restantes de trial
                            @endif
                        </p>

                        @if($log->user)
                            <p class="text-xs text-gray-500 mt-2">
                                <i class="fas fa-user mr-1"></i>
                                Por {{ $log->user->name }}
                            </p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-3">
                        <i class="fas fa-history"></i>
                    </div>
                    <p class="text-gray-500">Nenhuma atividade recente</p>
                    <p class="text-gray-400 text-sm mt-1">As alterações de plano aparecerão aqui</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-6">
            <div class="flex items-center">
                <div class="p-3 bg-white rounded-lg mr-4">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-blue-700">Utilizadores ativos</p>
                    <p class="text-2xl font-bold text-blue-900">{{ $usage['users'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-xl p-6">
            <div class="flex items-center">
                <div class="p-3 bg-white rounded-lg mr-4">
                    <i class="fas fa-folder text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-green-700">Projetos</p>
                    <p class="text-2xl font-bold text-green-900">{{ $usage['projects'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-xl p-6">
            <div class="flex items-center">
                <div class="p-3 bg-white rounded-lg mr-4">
                    <i class="fas fa-calendar-check text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm text-purple-700">Dias no sistema</p>
                    <p class="text-2xl font-bold text-purple-900">{{ $tenant->created_at->diffInDays() }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
