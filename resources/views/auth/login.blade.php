<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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
    <h2>Login</h2>

    @if ($errors->any())
        <p class="error">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="/login">
        @csrf
        <input name="email" placeholder="Email">
        <input name="password" type="password" placeholder="Password">
        <button>Entrar</button>
    </form>
</div>
</body>
</html>
