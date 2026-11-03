@extends('layouts.app')
@section('title', $motorcycle->name)
@section('content')
    <section class="section">
        <div class="hero-band">
            <div class="hero-photo">
                @if ($motorcycle->photoUrl())
                    <img src="{{ $motorcycle->photoUrl() }}" alt="{{ $motorcycle->name }}">
                @else
                    <div class="photo-missing">Foto pendiente de {{ $motorcycle->name }}</div>
                @endif
            </div>
            <div class="hero-copy">
                <p class="kicker">{{ $lineLabel }} · {{ $motorcycle->displacement }} cc</p>
                <h1>{{ $motorcycle->name }}</h1>
                <p class="lead">{{ $motorcycle->summary }}</p>
                <p class="muted rate-chip">Tasa: {{ \App\Support\Money::rate() }}</p>
                <div class="quote-box quote-box-compact">
                    <div><span class="muted">Precio</span><strong>{!! \App\Support\Money::bothLines($quote['price']) !!}</strong></div>
                    <div><span class="muted">Inicial</span><strong>{!! \App\Support\Money::bothLines($quote['initial']) !!}</strong></div>
                    <div><span class="muted">Saldo</span><strong>{!! \App\Support\Money::bothLines($quote['balance']) !!}</strong></div>
                    <div><span class="muted">{{ $quote['months'] }} cuotas</span><strong>{!! \App\Support\Money::bothLines($quote['monthly']) !!}</strong></div>
                </div>
                <div class="path-cards">
                    <button class="path-card path-card-online" type="button" data-open-online="{{ $motorcycle->slug }}">
                        <small>En línea</small>
                        <strong>Subo mis documentos ahora</strong>
                        <span>Checklist → cuenta → fotos. Queda pendiente de aprobación.</span>
                    </button>
                    <button class="path-card path-card-persona" type="button" data-open-persona="{{ $motorcycle->slug }}">
                        <small>En la sede</small>
                        <strong>Llevo mis documentos</strong>
                        <span>Solo la lista de recaudos. Sin cuenta ni formulario.</span>
                    </button>
                </div>
            </div>
        </div>
    </section>
@endsection
