<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            margin: 0;
            font-family: system-ui, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }
        header {
            background: #111827;
            color: white;
            padding: 1rem 2rem;
        }
        main {
            padding: 2rem;
            max-width: 1200px;
            margin: auto;
        }
        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,.08);
            margin-bottom: 1.5rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }
    </style>
</head>
<body>

<header>
    <strong>{{ session('tenant_name') ?? 'Tenant' }}</strong>
    <span style="float:right;">
        {{ auth()->user()->name }}
        |
        <form method="POST" action="/logout" style="display:inline">
            @csrf
            <button style="background:none;border:none;color:white;cursor:pointer">
                Sair
            </button>
        </form>
    </span>
</header>

<main>
    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @yield('content')
</main>

</body>
</html>
