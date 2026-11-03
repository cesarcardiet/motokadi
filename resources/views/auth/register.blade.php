@extends('layouts.app')
@section('title', 'Registro')
@section('content')
    <form class="auth-card" method="POST" action="{{ route('register') }}">
        @csrf
        <p class="kicker">Cliente</p>
        <h2>Crear cuenta</h2>
        <div class="form-grid">
            <div class="full">
                <label for="name">Nombre y apellido</label>
                <input id="name" name="name" value="{{ old('name') }}" required>
                @error('name')<div class="help">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="national_id">Cédula</label>
                <input id="national_id" name="national_id" value="{{ old('national_id') }}" placeholder="V-12345678" required>
                @error('national_id')<div class="help">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="phone">Teléfono</label>
                <input id="phone" name="phone" value="{{ old('phone') }}" required>
                @error('phone')<div class="help">{{ $message }}</div>@enderror
            </div>
            <div class="full">
                <label for="email">Correo</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email')<div class="help">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required>
                @error('password')<div class="help">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="password_confirmation">Confirmar</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
        </div>
        <div class="actions"><button class="btn" type="submit">Registrarme</button></div>
    </form>
@endsection
