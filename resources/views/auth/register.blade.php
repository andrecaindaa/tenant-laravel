<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Registar</title>
    <style>
        body {
            font-family: system-ui;
            background: #f5f7fb;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            background: white;
            padding: 2rem;
            width: 360px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,.1);
        }
        input, button {
            width: 100%;
            padding: .7rem;
            margin-bottom: 1rem;
        }
        button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
        }
        .error {
            color: #dc2626;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Criar conta</h2>

    @if ($errors->any())
        <p class="error">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="/register">
        @csrf
        <input name="name" placeholder="Nome" required>
        <input name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="password_confirmation" placeholder="Confirmar Password" required>
        <button>Criar conta</button>
    </form>

    <p style="text-align:center">
        <a href="{{ route('login') }}">Já tenho conta</a>
    </p>
</div>

</body>
</html>
