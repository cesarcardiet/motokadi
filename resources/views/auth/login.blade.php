@extends('layouts.app')
@section('title', 'Ingresar')
@section('content')
    <form class="auth-card" method="POST" action="{{ route('login') }}">
        @csrf
        <p class="kicker">Acceso</p>
        <h2>Ingresar</h2>
        <div class="form-grid">
            <div class="full">
                <label for="email">Correo</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                @error('email')<div class="help">{{ $message }}</div>@enderror
            </div>
            <div class="full">
                <label for="password">Contraseña</label>
                <input id="password" name="password" type="password" required>
            </div>
            <label class="check full"><input type="checkbox" name="remember" value="1"> Recordarme</label>
        </div>
        <div class="actions"><button class="btn" type="submit">Entrar</button></div>
        <p class="muted">¿No tienes cuenta? <a href="{{ route('register', request()->only('next')) }}">Regístrate</a></p>
        <div class="demo">
            <strong>Cuentas de prueba</strong><br>
            admin@kadimotors.test<br>
            vendedor.naguanagua@kadimotors.test<br>
            cliente@kadimotors.test<br>
            Contraseña: <code>Kadi2026!</code>
        </div>
    </form>
@endsection
