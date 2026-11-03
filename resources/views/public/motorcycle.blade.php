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
                <div class="quote-box">
                    <div><span class="muted">Precio</span><strong>{{ \App\Support\Money::usd($quote['price']) }}</strong></div>
                    <div><span class="muted">Inicial</span><strong>{{ \App\Support\Money::usd($quote['initial']) }}</strong></div>
                    <div><span class="muted">Saldo</span><strong>{{ \App\Support\Money::usd($quote['balance']) }}</strong></div>
                    <div><span class="muted">{{ $quote['months'] }} cuotas</span><strong>{{ \App\Support\Money::usd($quote['monthly']) }}</strong></div>
                </div>
                <button class="btn" type="button" data-open-moto="{{ $motorcycle->slug }}">Solicitar esta moto</button>
            </div>
        </div>
    </section>
@endsection
