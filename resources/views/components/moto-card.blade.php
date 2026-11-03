@props(['motorcycle'])
@php($quote = $motorcycle->quote())
<article class="bike-card">
    <a class="bike-hit" href="{{ route('catalog.show', $motorcycle) }}">
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
            <div class="bike-price">{!! \App\Support\Money::bothLines($quote['monthly']) !!} <span class="muted">/ mes</span></div>
        </div>
    </a>
    <div class="bike-card-actions bike-card-paths">
        <button class="bike-path bike-path-online" type="button" data-open-online="{{ $motorcycle->slug }}">Subo ahora</button>
        <button class="bike-path bike-path-persona" type="button" data-open-persona="{{ $motorcycle->slug }}">Llevo en persona</button>
    </div>
</article>
