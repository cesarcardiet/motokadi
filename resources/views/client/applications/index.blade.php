@extends('layouts.app')
@section('title', 'Mis solicitudes')
@section('content')
    <section class="hero">
        <div class="split applications-head">
            <div>
                <p class="kicker">Cliente</p>
                <h1>Mis solicitudes</h1>
                @php($active = auth()->user()->activeApplication())
                @if ($active)
                    <div class="flash error" style="margin-top:12px">Tienes una solicitud activa, espera la aprobación. <a href="{{ route('client.applications.show', $active) }}">Ver solicitud</a></div>
                @endif
            </div>
            <a class="btn" href="{{ route('catalog.index') }}">Elegir moto</a>
        </div>
        <div class="table-wrap applications-list">
            <table class="stack-table">
                <thead>
                    <tr><th>Modelo</th><th>Sede</th><th>Canal</th><th>Estado</th><th>Cuota</th></tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td data-label="Modelo"><a href="{{ route('client.applications.show', $application) }}">{{ $application->motorcycle->name }}</a></td>
                            <td data-label="Sede">{{ $application->branch->name }}</td>
                            <td data-label="Canal">{{ $application->channel->label() }}</td>
                            <td data-label="Estado">{{ $application->status->label() }}</td>
                            <td data-label="Cuota">{!! \App\Support\Money::bothLines($application->monthly_payment) !!}</td>
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
