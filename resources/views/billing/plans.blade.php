@extends('layouts.app')

@section('title', 'Planos e Preços')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-2">Planos e Preços</h1>
    <p class="text-gray-600 mb-8">Escolha o plano ideal para o seu negócio</p>

    <!-- Current Plan Banner -->
    @if($currentPlan)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold text-blue-800">Plano Atual: {{ $currentPlan->name }}</h3>
                    <p class="text-blue-600">{{ $currentPlan->description }}</p>
                    @if($tenant->onTrial())
                        <p class="text-amber-600 font-medium mt-2">
                            ⏰ Trial ativo - {{ $tenant->trialDaysLeft() }} dias restantes
                        </p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-2xl font-bold text-blue-800">
                        @if($currentPlan->price_monthly > 0)
                            {{ number_format($currentPlan->price_monthly, 2) }}€<span class="text-sm font-normal">/mês</span>
                        @else
                            Grátis
                        @endif
                    </p>
                    @if($currentPlan->price_yearly > 0)
                        <p class="text-gray-500">ou {{ number_format($currentPlan->price_yearly, 2) }}€/ano</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Plans Grid -->
    <div class="grid md:grid-cols-3 gap-8 mb-12">
        @foreach($plans as $plan)
            @php
                $isCurrent = $currentPlan && $currentPlan->id === $plan->id;
                $isDowngrade = $currentPlan && $plan->price_monthly < $currentPlan->price_monthly;
                $isUpgrade = $currentPlan && $plan->price_monthly > $currentPlan->price_monthly;
            @endphp

            <div class="border rounded-2xl p-8 {{ $isCurrent ? 'border-blue-500 ring-2 ring-blue-100' : 'border-gray-200' }}
                        {{ $plan->slug === 'pro' ? 'bg-gradient-to-b from-blue-50 to-white scale-105' : '' }}">

                <!-- Plan Header -->
                <div class="mb-6">
                    <h3 class="text-2xl font-bold mb-2">{{ $plan->name }}</h3>
                    <p class="text-gray-600 mb-4">{{ $plan->description }}</p>

                    <div class="mb-6">
                        <span class="text-4xl font-bold">{{ $plan->price_monthly > 0 ? number_format($plan->price_monthly, 2) . '€' : 'Grátis' }}</span>
                        @if($plan->price_monthly > 0)
                            <span class="text-gray-500">/mês</span>
                        @endif
                    </div>

                    @if($plan->price_yearly > 0)
                        <p class="text-gray-500 mb-6">
                            <strong>{{ number_format($plan->price_yearly, 2) }}€/ano</strong>
                            <span class="text-sm">(poupe {{ $plan->price_monthly * 12 - $plan->price_yearly }}€)</span>
                        </p>
                    @endif
                </div>

                <!-- Limits -->
                <div class="mb-8">
                    <h4 class="font-semibold mb-4">Limites incluídos</h4>
                    <ul class="space-y-3">
                        @foreach($plan->limits as $key => $limit)
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ ucfirst($key) }}: <strong>{{ $limit ?: 'Ilimitado' }}</strong></span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Features -->
                @if($plan->features && count($plan->features) > 0)
                    <div class="mb-8">
                        <h4 class="font-semibold mb-4">Funcionalidades</h4>
                        <ul class="space-y-2">
                            @foreach($plan->features as $feature)
                                <li class="text-gray-600">• {{ $feature }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Action Button -->
                <div class="mt-auto">
                    @if($isCurrent)
                        <button class="w-full py-3 px-4 bg-gray-100 text-gray-700 rounded-lg font-medium cursor-default">
                            Plano Atual
                        </button>
                    @elseif($isUpgrade)
                        <form method="POST" action="{{ route('billing.upgrade', $plan) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full py-3 px-4 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition">
                                Fazer Upgrade
                            </button>
                        </form>
                    @elseif($isDowngrade)
                        <form method="POST" action="{{ route('billing.downgrade', $plan) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-600 text-white rounded-lg font-medium transition">
                                Agendar Downgrade
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('billing.upgrade', $plan) }}">
                            @csrf
                            <button type="submit"
                                    class="w-full py-3 px-4 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition">
                                Selecionar Plano
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Trial Information -->
    @if($tenant->onTrial())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-8">
            <div class="flex items-start">
                <div class="text-amber-500 mr-4 text-2xl">⏰</div>
                <div>
                    <h3 class="text-lg font-semibold text-amber-800">Período de Trial Ativo</h3>
                    <p class="text-amber-700">
                        O seu trial termina em <strong>{{ $tenant->trialDaysLeft() }} dias</strong>
                        ({{ $trialEndsAt->format('d/m/Y') }}).
                    </p>
                    <p class="text-amber-600 mt-2">
                        Após o trial, será automaticamente transferido para o plano Free se não escolher um plano pago.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Billing History Link -->
    <div class="text-center">
        <a href="{{ route('billing.history') }}"
           class="inline-flex items-center text-blue-600 hover:text-blue-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Ver histórico de alterações de plano
        </a>
    </div>
</div>
@endsection
