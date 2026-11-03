@extends('layouts.app')
@section('title', 'Requisitos')
@section('content')
    <section class="section">
        <p class="kicker">La carpeta</p>
        <h1>Requisitos</h1>
        <p class="lead">Dos caminos claros. Elige cómo quieres presentar los recaudos.</p>
        <div class="path-cards path-cards-modes" style="max-width:720px">
            <button class="path-card path-card-online" type="button" data-open-online>
                <small>En línea</small>
                <strong>Subo mis documentos ahora</strong>
                <span>Confirmas la lista, entras con tu cuenta y subes fotos. La solicitud queda pendiente de aprobación.</span>
            </button>
            <button class="path-card path-card-persona" type="button" data-open-persona>
                <small>En la sede</small>
                <strong>Llevo mis documentos</strong>
                <span>Solo ves la lista (cédula frente, RIF, licencia 2DA, constancia). Sin cuenta ni formulario.</span>
            </button>
        </div>
        <div class="panel folder-panel" style="margin-top:22px">
            <h2>Qué lleva la carpeta</h2>
            <div class="steps folder-steps">
                @foreach (\App\Enums\DocumentType::required() as $index => $type)
                    <article class="step"><b>{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</b><h3>{{ $type->shortLabel() }}</h3><p>{{ $type->label() }}</p></article>
                @endforeach
            </div>
            <a class="btn" href="{{ route('catalog.index') }}">Elegir moto</a>
        </div>
    </section>
@endsection
