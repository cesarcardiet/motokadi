@extends('layouts.app')
@section('title', 'Bandeja')
@section('content')
    <section class="hero">
        <p class="kicker">Sede</p>
        <h1>Bandeja</h1>
        <p class="lead muted">{{ $activeCount }} solicitud{{ $activeCount === 1 ? '' : 'es' }} activa{{ $activeCount === 1 ? '' : 's' }} pendientes de cierre.</p>
        <div class="filters">
            <a href="{{ route('staff.applications.index', ['estado' => 'activas']) }}" @class(['active' => $status === 'activas'])>Activas</a>
            <a href="{{ route('staff.applications.index', ['estado' => 'todas']) }}" @class(['active' => $status === 'todas'])>Todas</a>
            @foreach ($statuses as $item)
                <a href="{{ route('staff.applications.index', ['estado' => $item->value]) }}" @class(['active' => $status === $item->value])>{{ $item->label() }}</a>
            @endforeach
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Solicitante</th><th>WhatsApp</th><th>Modelo</th><th>Sede</th><th>Canal</th><th>Estado</th></tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td>
                                <a href="{{ route('staff.applications.show', $application) }}">{{ $application->client->name }}</a>
                                <div class="muted">{{ $application->client->national_id }}</div>
                            </td>
                            <td>{{ $application->whatsapp_phone ?: ($application->client->phone ?: '—') }}</td>
                            <td>{{ $application->motorcycle->name }}</td>
                            <td>{{ $application->branch->city }}</td>
                            <td>{{ $application->channel->label() }}</td>
                            <td>{{ $application->status->label() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay solicitudes en esta bandeja.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $applications->links('partials.pager') }}
    </section>
@endsection
