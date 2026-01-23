@extends('layouts.app')

@section('title', 'Configuração Inicial')

@section('styles')
<style>
    .onboarding-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .progress-container {
        margin-bottom: 3rem;
    }
    .progress-bar {
        height: 8px;
        background: #e5e7eb;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 1rem;
    }
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transition: width 0.5s ease;
    }
    .progress-text {
        text-align: center;
        color: #6b7280;
        font-size: 0.875rem;
    }
    .steps-nav {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }
    .steps-nav::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: #e5e7eb;
        z-index: 1;
    }
    .step-circle {
        position: relative;
        z-index: 2;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: 3px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: #9ca3af;
        transition: all 0.3s;
    }
    .step-circle.active {
        border-color: #667eea;
        background: #667eea;
        color: white;
        transform: scale(1.1);
    }
    .step-circle.completed {
        border-color: #10b981;
        background: #10b981;
        color: white;
    }
    .step-label {
        position: absolute;
        top: 50px;
        left: 50%;
        transform: translateX(-50%);
        white-space: nowrap;
        color: #6b7280;
        font-size: 0.875rem;
    }
    .step-content {
        background: white;
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
    .btn-group {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #e5e7eb;
    }
    .btn-prev, .btn-next, .btn-skip {
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.3s;
    }
    .btn-prev {
        background: #f3f4f6;
        color: #374151;
    }
    .btn-prev:hover {
        background: #e5e7eb;
    }
    .btn-next {
        background: #667eea;
        color: white;
        margin-left: auto;
    }
    .btn-next:hover {
        background: #5a67d8;
    }
    .btn-skip {
        background: transparent;
        color: #6b7280;
        text-decoration: underline;
    }
    .btn-skip:hover {
        color: #374151;
    }
    .onboarding-icon {
        font-size: 3rem;
        text-align: center;
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="onboarding-container">
    <!-- Progress -->
    <div class="progress-container">
        <div class="progress-bar">
            <div class="progress-fill" style="width: {{ $progress }}%"></div>
        </div>
        <p class="progress-text">{{ $progress }}% concluído</p>
    </div>

    <!-- Steps Navigation -->
    <div class="steps-nav">
        @foreach($steps as $key => $step)
            <div style="text-align: center;">
                <div class="step-circle
                    {{ $key === $currentStep ? 'active' : '' }}
                    {{ $step['completed'] ? 'completed' : '' }}">
                    @if($step['completed'])
                        ✓
                    @else
                        {{ array_search($key, array_keys($steps)) + 1 }}
                    @endif
                </div>
                <div class="step-label">{{ $step['title'] }}</div>
            </div>
        @endforeach
    </div>

    <!-- Step Content -->
    <div class="step-content">
        @if($currentStep === 'welcome')
            <div class="onboarding-icon">👋</div>
            <h2 class="text-2xl font-bold mb-4">Bem-vindo ao {{ $tenant->name }}!</h2>
            <p class="mb-4 text-gray-600">Vamos configurar o seu workspace para começar a usar todas as funcionalidades.</p>
            <form method="POST" action="{{ url("/onboarding/welcome") }}">
                @csrf
                <button type="submit" class="btn-next">Começar Configuração →</button>
            </form>

        @elseif($currentStep === 'branding')
            <div class="onboarding-icon">🎨</div>
            <h2 class="text-2xl font-bold mb-4">Personalizar Aparência</h2>
            <form method="POST" action="{{ url("/onboarding/branding") }}">
                @csrf
                <div class="form-group">
                    <label>Nome da Empresa</label>
                    <input type="text" name="company_name" value="{{ $tenant->name }}" class="w-full p-3 border rounded-lg">
                </div>
                <div class="form-group mt-4">
                    <label>Logotipo (URL)</label>
                    <input type="url" name="logo_url" placeholder="https://exemplo.com/logo.png" class="w-full p-3 border rounded-lg">
                </div>
                <div class="form-group mt-4">
                    <label>Cor do Tema</label>
                    <input type="color" name="primary_color" value="#667eea" class="w-full h-10">
                </div>
                <div class="btn-group">
                    <button type="button" onclick="history.back()" class="btn-prev">← Voltar</button>
                    <button type="submit" class="btn-next">Continuar →</button>
                </div>
            </form>

        @elseif($currentStep === 'team')
            <div class="onboarding-icon">👥</div>
            <h2 class="text-2xl font-bold mb-4">Convidar Equipa</h2>
            <p class="mb-4 text-gray-600">Convide membros para colaborar no seu workspace.</p>
            <form method="POST" action="{{ url("/onboarding/team") }}">
                @csrf
                <div class="form-group">
                    <label>Email do Membro</label>
                    <input type="email" name="email" placeholder="colaborador@empresa.com" class="w-full p-3 border rounded-lg" required>
                </div>
                <div class="form-group mt-4">
                    <label>Cargo</label>
                    <select name="role" class="w-full p-3 border rounded-lg">
                        <option value="admin">Administrador</option>
                        <option value="member" selected>Membro</option>
                        <option value="viewer">Visualizador</option>
                    </select>
                </div>
                <div class="btn-group">
                    <button type="button" onclick="history.back()" class="btn-prev">← Voltar</button>
                    <button type="submit" class="btn-next">Enviar Convite →</button>
                    <button type="button" onclick="skipStep()" class="btn-skip">Saltar por agora</button>
                </div>
            </form>

        @elseif($currentStep === 'settings')
            <div class="onboarding-icon">⚙️</div>
            <h2 class="text-2xl font-bold mb-4">Definições do Sistema</h2>
            <form method="POST" action="{{ url("/onboarding/settings") }}">
                @csrf
                <div class="form-group">
                    <label>Fuso Horário</label>
                    <select name="timezone" class="w-full p-3 border rounded-lg">
                        @foreach(timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" {{ $tz === 'Europe/Lisbon' ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mt-4">
                    <label>Idioma</label>
                    <select name="language" class="w-full p-3 border rounded-lg">
                        <option value="pt">Português</option>
                        <option value="en">English</option>
                        <option value="es">Español</option>
                    </select>
                </div>
                <div class="form-group mt-4">
                    <label>Formato de Data</label>
                    <select name="date_format" class="w-full p-3 border rounded-lg">
                        <option value="d/m/Y">DD/MM/AAAA</option>
                        <option value="Y-m-d">AAAA-MM-DD</option>
                        <option value="m/d/Y">MM/DD/AAAA</option>
                    </select>
                </div>
                <div class="btn-group">
                    <button type="button" onclick="history.back()" class="btn-prev">← Voltar</button>
                    <button type="submit" class="btn-next">Guardar →</button>
                </div>
            </form>

        @elseif($currentStep === 'finish')
            <div class="onboarding-icon">🎉</div>
            <h2 class="text-2xl font-bold mb-4">Configuração Completa!</h2>
            <p class="mb-4 text-gray-600">O seu workspace está pronto para usar. Pode começar a:</p>
            <ul class="mb-6 space-y-2">
                <li>✅ Criar projetos</li>
                <li>✅ Convidar mais membros</li>
                <li>✅ Personalizar o dashboard</li>
                <li>✅ Explorar funcionalidades</li>
            </ul>
            <form method="POST" action="{{ url("/onboarding/finish") }}">
                @csrf
                <div class="btn-group">
                    <button type="button" onclick="history.back()" class="btn-prev">← Voltar</button>
                    <button type="submit" class="btn-next">Ir para o Dashboard →</button>
                </div>
            </form>
        @endif
    </div>

    <!-- Skip All -->
    <div class="text-center mt-8">
        <form method="POST" action="{{ route('onboarding.skip') }}">
            @csrf
            <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm">
                Saltar toda a configuração inicial
            </button>
        </form>
    </div>
</div>

<script>
function skipStep() {
    fetch('{{ route("onboarding.skip") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }).then(() => {
        window.location.href = '{{ route("dashboard") }}';
    });
}
</script>
@endsection
