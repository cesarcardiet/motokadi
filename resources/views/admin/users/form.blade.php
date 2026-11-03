@extends('layouts.app')
@section('title', $userModel->exists ? 'Editar usuario' : 'Nuevo usuario')
@section('content')
    <form class="auth-card" method="POST" action="{{ $userModel->exists ? route('admin.users.update', $userModel) : route('admin.users.store') }}">
        @csrf
        @if ($userModel->exists) @method('PUT') @endif
        <h2>{{ $userModel->exists ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
        <div class="form-grid">
            <div class="full"><label for="name">Nombre</label><input id="name" name="name" value="{{ old('name', $userModel->name) }}" required>@error('name')<div class="help">{{ $message }}</div>@enderror</div>
            <div><label for="email">Correo</label><input id="email" type="email" name="email" value="{{ old('email', $userModel->email) }}" required>@error('email')<div class="help">{{ $message }}</div>@enderror</div>
            <div><label for="phone">Teléfono</label><input id="phone" name="phone" value="{{ old('phone', $userModel->phone) }}"></div>
            <div><label for="national_id">Cédula</label><input id="national_id" name="national_id" value="{{ old('national_id', $userModel->national_id) }}"></div>
            <div>
                <label for="role_id">Rol</label>
                <select id="role_id" name="role_id" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string) old('role_id', $userModel->role_id) === (string) $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="branch_id">Sede</label>
                <select id="branch_id" name="branch_id">
                    <option value="">Sin sede</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $userModel->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                @error('branch_id')<div class="help">{{ $message }}</div>@enderror
            </div>
            <div><label for="password">Contraseña</label><input id="password" type="password" name="password" @unless($userModel->exists) required @endunless></div>
            <div><label for="password_confirmation">Confirmar</label><input id="password_confirmation" type="password" name="password_confirmation"></div>
            @error('password')<div class="help full">{{ $message }}</div>@enderror
            <label class="check full"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $userModel->is_active) == true)> Cuenta activa</label>
        </div>
        <div class="actions"><button class="btn" type="submit">Guardar</button></div>
    </form>
@endsection
