<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Selecionar Empresa</title>

    <style>
        body {
            font-family: sans-serif;
            background: #f5f5f5;
        }

        .box {
            max-width: 500px;
            margin: 80px auto;
            background: white;
            padding: 24px;
            border-radius: 6px;
        }

        button, input {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Selecionar empresa</h2>

    @forelse($tenants as $tenant)
        <form method="POST" action="/select-tenant/{{ $tenant->id }}">
            @csrf
            <button type="submit">
                {{ $tenant->name }}
            </button>
        </form>
    @empty
        <p>Não tens empresas associadas.</p>
    @endforelse

    <hr>

    <h3>Criar nova empresa</h3>

    <form method="POST" action="/tenants">
        @csrf
        <input name="name" placeholder="Nome da empresa" required>
        <button>Criar</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button style="background:#eee;margin-top:20px;">
            Logout
        </button>
    </form>
</div>

</body>
</html>
