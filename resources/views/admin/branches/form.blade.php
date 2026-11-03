@extends('layouts.app')
@section('title', $branch->exists ? 'Editar sede' : 'Nueva sede')
@section('content')
    <form class="auth-card" method="POST" action="{{ $branch->exists ? route('admin.branches.update', $branch) : route('admin.branches.store') }}">
        @csrf
        @if ($branch->exists) @method('PUT') @endif
        <h2>{{ $branch->exists ? 'Editar sede' : 'Nueva sede' }}</h2>
        <div class="form-grid">
            <div class="full"><label for="name">Nombre</label><input id="name" name="name" value="{{ old('name', $branch->name) }}" required></div>
            <div><label for="city">Ciudad</label><input id="city" name="city" value="{{ old('city', $branch->city) }}" required></div>
            <div><label for="phone">Teléfono</label><input id="phone" name="phone" value="{{ old('phone', $branch->phone) }}"></div>
            <div class="full"><label for="address">Dirección</label><input id="address" name="address" value="{{ old('address', $branch->address) }}" required></div>
            <label class="check full"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $branch->is_active) == true)> Activa</label>
        </div>
        <div class="actions"><button class="btn" type="submit">Guardar</button></div>
    </form>
@endsection
