@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
    <section class="hero">
        <div class="split">
            <h1>Usuarios</h1>
            <a class="btn" href="{{ route('admin.users.create') }}">Nuevo usuario</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Nombre</th><th>Rol</th><th>Sede</th><th>Correo</th><th></th></tr></thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }} @unless($user->is_active)<span class="badge">Inactivo</span>@endunless</td>
                            <td>{{ $user->role?->name }}</td>
                            <td>{{ $user->branch?->city ?? '—' }}</td>
                            <td>{{ $user->email }}</td>
                            <td><a href="{{ route('admin.users.edit', $user) }}">Editar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links('partials.pager') }}
    </section>
@endsection
