<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selecionar Tenant</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #718096;
            text-align: center;
            margin-bottom: 30px;
        }
        .tenants-grid {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }
        .tenant-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .tenant-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.1);
        }
        .tenant-card.active {
            border-color: #48bb78;
            background: #f0fff4;
        }
        .tenant-info h3 {
            color: #2d3748;
            margin-bottom: 5px;
        }
        .tenant-info p {
            color: #718096;
            font-size: 14px;
        }
        .tenant-plan {
            background: #edf2f7;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .tenant-plan.free { background: #c6f6d5; color: #22543d; }
        .tenant-plan.pro { background: #bee3f8; color: #2c5282; }
        .tenant-plan.business { background: #e9d8fd; color: #553c9a; }
        form button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        form button:hover {
            background: #5a67d8;
        }
        .divider {
            display: flex;
            align-items: center;
            margin: 30px 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        .divider span {
            padding: 0 15px;
            color: #718096;
            font-size: 14px;
        }
        .create-section {
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .create-section:hover {
            border-color: #667eea;
            background: #f7fafc;
        }
        .create-section h3 {
            color: #4a5568;
            margin-bottom: 10px;
        }
        .create-section p {
            color: #718096;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .btn-create {
            display: inline-block;
            padding: 10px 20px;
            background: #48bb78;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.3s;
        }
        .btn-create:hover {
            background: #38a169;
        }
        .logout-btn {
            width: 100%;
            padding: 12px;
            background: #fed7d7;
            color: #c53030;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }
        .logout-btn:hover {
            background: #feb2b2;
        }
        .current-session {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Selecionar Workspace</h1>
        <p class="subtitle">Escolha uma empresa para continuar</p>

        <div class="tenants-grid">
            @forelse($tenants as $tenant)
                @php
                    $plan = $tenant->subscription?->plan;
                    $planClass = strtolower($plan->slug ?? 'free');
                    $isActive = session('tenant_id') == $tenant->id;
                @endphp
                <form method="POST" action="{{ route('tenant.select', $tenant) }}">
                    @csrf
                    <button type="submit" class="tenant-card {{ $isActive ? 'active' : '' }}">
                        <div class="tenant-info">
                            <h3>{{ $tenant->name }}</h3>
                            <p>{{ $tenant->slug }}</p>
                        </div>
                        <div class="tenant-plan {{ $planClass }}">
                            {{ $plan->name ?? 'Free' }}
                        </div>
                    </button>
                </form>
            @empty
                <div style="text-align: center; padding: 40px;">
                    <p style="color: #718096;">Nenhuma empresa encontrada.</p>
                </div>
            @endforelse
        </div>

        <div class="divider">
            <span>OU</span>
        </div>

        <div class="create-section">
            <h3>Criar Nova Empresa</h3>
            <p>Crie um novo workspace para sua equipa</p>
            <a href="{{ route('tenants.create') }}" class="btn-create">
                Criar Nova Empresa
            </a>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">
                Terminar Sessão
            </button>
        </form>

        <div class="current-session">
            <p>Conectado como: <strong>{{ auth()->user()->name }}</strong></p>
            <p>{{ auth()->user()->email }}</p>
        </div>
    </div>
</body>
</html>
