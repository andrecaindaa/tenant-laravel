@extends('layouts.app')

@section('title', 'Administração - Planos')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Administração de Planos</h1>
    <p class="text-gray-600 mt-2">Crie e gerencie os planos disponíveis no sistema</p>
</div>

<div class="mb-6">
    <a href="{{ route('admin.plans.create') }}"
       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        <i class="fas fa-plus mr-2"></i>
        Criar novo plano
    </a>
</div>

@if($plans->isEmpty())
    <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
        <div class="text-gray-400 mb-4">
            <i class="fas fa-layer-group text-4xl"></i>
        </div>
        <p class="text-gray-500">Nenhum plano criado ainda</p>
        <p class="text-gray-400 text-sm mt-1">Crie o primeiro plano</p>
    </div>
@else
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Limites</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($plans as $plan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $plan->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <code class="text-sm text-gray-500">{{ $plan->slug }}</code>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-gray-900">
                                {{ number_format($plan->price, 2, ',', '.') }}€
                                @if($plan->interval)
                                    <span class="text-gray-500 text-sm">/{{ $plan->interval === 'yearly' ? 'ano' : 'mês' }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                @foreach($plan->limits ?? [] as $key => $value)
                                    <div class="text-sm">
                                        <span class="text-gray-500">{{ $key }}:</span>
                                        <span class="ml-1 font-medium">
                                            @if($value === null || $value === 0)
                                                ∞
                                            @elseif($value === -1)
                                                Não permitido
                                            @else
                                                {{ $value }}
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $plan->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $plan->active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="#" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <form action="#" method="POST" class="inline" onsubmit="return confirm('Tem certeza?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<!-- Link de retorno -->
<div class="mt-8 pt-8 border-t border-gray-200">
    <a href="{{ route('dashboard') }}"
       class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
        <i class="fas fa-arrow-left mr-2"></i>
        Voltar ao Dashboard
    </a>
</div>
@endsection
