@extends('layouts.app')
@section('title', 'Administración')
@section('content')
    <section class="hero">
        <p class="kicker">Panel</p>
        <h1>Administración</h1>
        <div class="filters">
            <a class="active" href="{{ route('admin.dashboard') }}">Resumen</a>
            <a href="{{ route('admin.branches.index') }}">Sedes</a>
            <a href="{{ route('admin.users.index') }}">Usuarios</a>
            <a href="{{ route('admin.motorcycles.index') }}">Motos</a>
            <a href="{{ route('admin.roles.index') }}">Roles</a>
            <a href="{{ route('staff.applications.index') }}">Bandeja</a>
        </div>
        <div class="stat-row">
            <div class="stat"><span class="muted">Modelos activos</span><strong>{{ $motorcycles }}</strong></div>
            <div class="stat"><span class="muted">Sedes</span><strong>{{ $branches }}</strong></div>
            <div class="stat"><span class="muted">Vendedores</span><strong>{{ $sellers }}</strong></div>
        </div>
        <div class="stat-row" style="margin-top:12px">
            @foreach ($statuses as $status)
                <div class="stat"><span class="muted">{{ $status->label() }}</span><strong>{{ $counts[$status->value] ?? 0 }}</strong></div>
            @endforeach
        </div>
        <h2 style="margin-top:28px">Últimas solicitudes</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Cliente</th><th>Moto</th><th>Sede</th><th>Estado</th></tr></thead>
                <tbody>
                    @forelse ($recent as $application)
                        <tr>
                            <td><a href="{{ route('staff.applications.show', $application) }}">{{ $application->client->name }}</a></td>
                            <td>{{ $application->motorcycle->name }}</td>
                            <td>{{ $application->branch->city }}</td>
                            <td>{{ $application->status->label() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Aún no hay solicitudes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
