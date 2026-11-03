@extends('layouts.app')
@section('title', 'Catálogo')
@section('content')
    <section class="stage">
        <p class="kicker">Línea Kadi</p>
        <h1>Toda la línea</h1>
        <p class="lead">Toca una moto para ver la cuota y empezar: subir documentos y esperar la llamada, o llevar la carpeta a la sede.</p>
        <div class="filters">
            @foreach ($filters as $key => $label)
                <a href="{{ route('catalog.index', $key === '' ? [] : ['linea' => $key]) }}" @class(['active' => $line === $key])>{{ $label }} ({{ $counts[$key] }})</a>
            @endforeach
        </div>
        <div class="bike-grid">
            @foreach ($motorcycles as $motorcycle)
                <x-moto-card :motorcycle="$motorcycle" />
            @endforeach
        </div>
        {{ $motorcycles->links('partials.pager') }}
    </section>
@endsection
