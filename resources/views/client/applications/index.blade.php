@extends('layouts.app')
@section('title', 'Mis solicitudes')
@section('content')
    <section class="hero">
        <div class="split">
            <div>
                <p class="kicker">Cliente</p>
                <h1>Mis solicitudes</h1>
            </div>
            <a class="btn" href="{{ route('catalog.index') }}">Elegir moto</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Modelo</th><th>Sede</th><th>Canal</th><th>Estado</th><th>Cuota</th></tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td><a href="{{ route('client.applications.show', $application) }}">{{ $application->motorcycle->name }}</a></td>
                            <td>{{ $application->branch->name }}</td>
                            <td>{{ $application->channel->label() }}</td>
                            <td>{{ $application->status->label() }}</td>
                            <td>{{ \App\Support\Money::usd($application->monthly_payment) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Todavía no tienes solicitudes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $applications->links('partials.pager') }}
    </section>
@endsection
