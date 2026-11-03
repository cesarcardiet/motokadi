@extends('layouts.app')
@section('title', 'Sedes')
@section('content')
    <section class="hero">
        <div class="split">
            <h1>Sedes</h1>
            <a class="btn" href="{{ route('admin.branches.create') }}">Nueva sede</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Sede</th><th>Ciudad</th><th>Dirección</th><th>Usuarios</th><th>Solicitudes</th><th></th></tr></thead>
                <tbody>
                    @foreach ($branches as $branch)
                        <tr>
                            <td>{{ $branch->name }} @unless($branch->is_active)<span class="badge">Inactiva</span>@endunless</td>
                            <td>{{ $branch->city }}</td>
                            <td>{{ $branch->address }}</td>
                            <td>{{ $branch->users_count }}</td>
                            <td>{{ $branch->applications_count }}</td>
                            <td><a href="{{ route('admin.branches.edit', $branch) }}">Editar</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $branches->links('partials.pager') }}
    </section>
@endsection
