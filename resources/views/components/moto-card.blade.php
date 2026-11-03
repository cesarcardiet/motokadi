@props(['motorcycle'])
@php($quote = $motorcycle->quote())
<article class="bike-card">
    <button class="bike-hit" type="button" data-open-moto="{{ $motorcycle->slug }}">
        <div class="bike-photo">
            @if ($motorcycle->photoUrl())
                <img src="{{ $motorcycle->photoUrl() }}" alt="{{ $motorcycle->name }}">
            @else
                <div class="photo-missing">Foto pendiente<br>{{ $motorcycle->name }}</div>
            @endif
            <span class="cc">{{ $motorcycle->displacement }}CC</span>
        </div>
        <div class="bike-copy">
            <h3>{{ str_replace('Kadi ', '', $motorcycle->name) }}</h3>
            <div class="tag">{{ $motorcycle->lineLabel() }}</div>
            <p>{{ $motorcycle->summary }}</p>
            <div class="bike-price">{{ \App\Support\Money::usd($quote['monthly']) }} <span class="muted">/ mes</span></div>
        </div>
    </button>
</article>
