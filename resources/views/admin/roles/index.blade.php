@extends('layouts.app')
@section('title', 'Roles y permisos')
@section('content')
    <section class="hero">
        <p class="kicker">Acceso</p>
        <h1>Roles y permisos</h1>
        <p class="lead">El administrador cierra el crédito. El vendedor llega hasta la preaprobación de su sede. El cliente solo arma su solicitud.</p>
        @foreach ($roles as $role)
            <form class="panel" method="POST" action="{{ route('admin.roles.update', $role) }}">
                @csrf
                @method('PUT')
                <h2>{{ $role->name }}</h2>
                <p class="muted">{{ $role->description }}</p>
                <div class="form-grid">
                    @foreach ($permissions as $permission)
                        <label class="check">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id))>
                            <span>{{ $permission->name }} <small class="muted">{{ $permission->group }}</small></span>
                        </label>
                    @endforeach
                </div>
                <div class="actions"><button class="btn small" type="submit">Guardar {{ $role->name }}</button></div>
            </form>
        @endforeach
    </section>
@endsection
