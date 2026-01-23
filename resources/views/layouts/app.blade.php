<!DOCTYPE html>
<html lang="pt" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tenant Manager')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @yield('styles')
</head>

<body class="h-full bg-gray-50">

<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 h-16 flex justify-between items-center">

        <!-- Logo -->
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-layer-group text-white text-sm"></i>
            </div>
            <span class="font-bold text-lg">
                Tenant<span class="text-blue-600">Manager</span>
            </span>
        </a>

        @auth
        <!-- Tenant selector - MOSTRAR APENAS SE $tenant EXISTIR -->
        @if(isset($tenant) && $tenant)
            <div class="hidden lg:block w-96" x-data="{ open:false }">
                <button @click="open = !open"
                        class="w-full bg-gray-50 border rounded-lg px-4 py-2 flex justify-between items-center hover:bg-gray-100 transition-colors">
                    <div class="text-left">
                        <p class="font-medium text-gray-900 truncate">
                            {{ $tenant->name ?? 'Selecionar empresa' }}
                        </p>
                        <p class="text-xs text-gray-500 truncate">
                            @php
                                $currentPlan = $tenant->currentPlan();
                            @endphp
                            {{ $currentPlan->name ?? 'Sem plano' }}
                            @if($tenant->onTrial())
                                • {{ $tenant->trialDaysLeft() }} dias trial
                            @endif
                        </p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 transition-transform duration-200"
                       :class="{ 'rotate-180': open }"></i>
                </button>

                <div x-show="open" @click.outside="open=false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute mt-2 w-96 bg-white border border-gray-200 rounded-lg shadow-xl z-50">
                    @foreach(auth()->user()->tenants as $t)
                        <form method="POST" action="{{ route('tenant.select', $t) }}">
                            @csrf
                            <button class="w-full px-4 py-3 text-left hover:bg-gray-50 flex items-center justify-between transition-colors
                                {{ session('tenant_id') == $t->id ? 'bg-blue-50' : '' }}">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 truncate">{{ $t->name }}</p>
                                    <p class="text-xs text-gray-500 truncate">
                                        {{ $t->currentPlan()->name ?? 'Free' }}
                                    </p>
                                </div>
                                @if(session('tenant_id') == $t->id)
                                    <i class="fas fa-check text-blue-500 ml-2"></i>
                                @endif
                            </button>
                        </form>
                    @endforeach

                    <a href="{{ route('tenants.create') }}"
                       class="block px-4 py-3 text-blue-600 hover:bg-blue-50 border-t border-gray-200 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Criar nova empresa
                    </a>
                </div>
            </div>
        @else
            <!-- Mostrar apenas link para criar tenant quando não há tenant selecionado -->
            <div class="text-center">
                <a href="{{ route('tenants.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>
                    Criar primeira empresa
                </a>
            </div>
        @endif
        @endauth

        <!-- User Menu -->
        <div class="relative" x-data="{ userMenu: false }">
            @auth
                <button @click="userMenu = !userMenu"
                        @click.outside="userMenu = false"
                        class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-blue-200 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600"></i>
                    </div>
                    <div class="text-left hidden md:block">
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-sm hidden md:block"></i>
                </button>

                <div x-show="userMenu"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-xl py-1 z-50">
                    <div class="px-4 py-2 border-b border-gray-200">
                        <p class="text-sm font-medium text-gray-900">Minha Conta</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>

                    @if(isset($tenant) && $tenant)
                        <a href="{{ route('dashboard') }}"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-tachometer-alt mr-3 text-gray-400"></i>
                            Dashboard
                        </a>

                        <a href="{{ route('billing.plans') }}"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-credit-card mr-3 text-gray-400"></i>
                            Planos
                        </a>

                        <a href="{{ route('billing.history') }}"
                           class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-history mr-3 text-gray-400"></i>
                            Histórico
                        </a>
                    @endif

                    <div class="border-t border-gray-200 my-1"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50 flex items-center">
                            <i class="fas fa-sign-out-alt mr-3"></i>
                            Sair
                        </button>
                    </form>
                </div>
            @else
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}"
                       class="text-gray-700 hover:text-gray-900 font-medium">
                        Login
                    </a>
                    <a href="{{ route('register') }}"
                       class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 font-medium">
                        Registar
                    </a>
                </div>
            @endauth
        </div>
    </div>
</nav>

<main class="max-w-7xl mx-auto px-4 py-8">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center">
            <i class="fas fa-check-circle text-green-500 mr-3"></i>
            <p class="text-green-800 font-medium flex-1">{{ session('success') }}</p>
            <button onclick="this.parentElement.remove()">
                <i class="fas fa-times text-green-500 hover:text-green-700"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
            <p class="text-red-800 font-medium flex-1">{{ session('error') }}</p>
            <button onclick="this.parentElement.remove()">
                <i class="fas fa-times text-red-500 hover:text-red-700"></i>
            </button>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg flex items-center">
            <i class="fas fa-info-circle text-blue-500 mr-3"></i>
            <p class="text-blue-800 font-medium flex-1">{{ session('info') }}</p>
            <button onclick="this.parentElement.remove()">
                <i class="fas fa-times text-blue-500 hover:text-blue-700"></i>
            </button>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center">
            <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
            <p class="text-yellow-800 font-medium flex-1">{{ session('warning') }}</p>
            <button onclick="this.parentElement.remove()">
                <i class="fas fa-times text-yellow-500 hover:text-yellow-700"></i>
            </button>
        </div>
    @endif

    <!-- Page Content -->
    @yield('content')
</main>

<footer class="border-t bg-white py-6 text-center text-sm text-gray-500 mt-12">
    © {{ date('Y') }} TenantManager. Todos os direitos reservados.
</footer>

<script>
    // Auto-dismiss flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            document.querySelectorAll('[class*="bg-"]').forEach(alert => {
                if (alert.querySelector('.fa-times')) {
                    alert.remove();
                }
            });
        }, 5000);
    });
</script>

@yield('scripts')
</body>
</html>
