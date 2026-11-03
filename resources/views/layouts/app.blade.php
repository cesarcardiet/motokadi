<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Financiamiento') · Kadi Motors</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,700;1,700&family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kadi.css') }}?v=19">
</head>
<body>
    <header class="topbar">
        <div class="wrap topbar-inner">
            <a class="brand" href="{{ route('home') }}">
                <img class="logo-mark" src="{{ asset('images/logo-kadi.png') }}" alt="Kadi">
            </a>
            <nav class="nav">
                <a href="{{ route('catalog.index') }}">Catálogo</a>
                <a href="{{ route('home') }}#calculadora">Calculadora</a>
                <a href="{{ route('simulation') }}">Cuotas</a>
                <a href="{{ route('requirements') }}">Requisitos</a>
                @auth
                    @if (auth()->user()->hasPermission('panel.admin'))
                        <a href="{{ route('admin.dashboard') }}">Administración</a>
                    @endif
                    @if (auth()->user()->hasPermission('solicitudes.revisar'))
                        <a href="{{ route('staff.applications.index') }}">Bandeja</a>
                    @endif
                    @if (auth()->user()->hasPermission('solicitudes.crear'))
                        <a href="{{ route('client.applications.index') }}">Mis solicitudes</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn small ghost" type="submit">Salir</button></form>
                @else
                    <a href="{{ route('login') }}">Ingresar</a>
                    <a class="btn small" href="{{ route('register') }}">Crear cuenta</a>
                @endauth
            </nav>
        </div>
    </header>
    <main class="wrap">
        @if (session('status')) <div class="flash">{{ session('status') }}</div> @endif
        @if (session('error')) <div class="flash error">{{ session('error') }}</div> @endif
        @yield('content')
    </main>
    <footer>
        <div class="wrap footer-inner">
            <div>
                <strong>Kadi Motors</strong>
                <p>Cuotas a 9 meses. Classic, Diamante y Tundra usan la tabla de ejemplo. El resto es precio de referencia con la misma fórmula.</p>
            </div>
            <div>
                <a href="{{ route('requirements') }}">Requisitos</a><br>
                <a href="{{ route('simulation') }}">Simulación</a>
            </div>
        </div>
    </footer>
    @include('partials.apply-modal')
</body>
</html>
