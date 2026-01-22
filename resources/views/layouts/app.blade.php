<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Tenant Project')</title>

    <style>
        body {
            font-family: sans-serif;
            background: #f5f5f5;
            margin: 0;
        }

        header {
            background: #111;
            color: white;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        main {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .badge {
            background: #eee;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        .warning {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 10px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<header>
    <strong>{{ $tenant->name ?? 'Dashboard' }}</strong>

    @auth
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button>Logout</button>
        </form>
    @endauth
</header>

<main>
    @yield('content')
</main>

</body>
</html>
