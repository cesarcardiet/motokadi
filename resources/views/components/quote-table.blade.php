@props(['motorcycles'])
@php($months = $motorcycles->first()?->term_months ?? 9)
<div class="quote-list">
    @foreach ($motorcycles as $motorcycle)
        @php($quote = $motorcycle->quote())
        <article class="quote-card">
            <a class="quote-card-model" href="{{ route('catalog.show', $motorcycle) }}">
                <strong>{{ $motorcycle->name }}</strong>
                <span>{{ $motorcycle->lineLabel() }} · {{ $motorcycle->displacement }} cc</span>
            </a>
            <div class="quote-card-monthly">
                <span>Cuota {{ $months }} meses</span>
                <strong>{!! \App\Support\Money::bothLines($quote['monthly']) !!}</strong>
            </div>
            <div class="quote-card-grid">
                <div>
                    <span>Precio</span>
                    <strong>{!! \App\Support\Money::bothLines($quote['price']) !!}</strong>
                </div>
                <div>
                    <span>Inicial</span>
                    <strong>{!! \App\Support\Money::bothLines($quote['initial']) !!}</strong>
                </div>
                <div>
                    <span>Saldo</span>
                    <strong>{!! \App\Support\Money::bothLines($quote['balance']) !!}</strong>
                </div>
            </div>
        </article>
    @endforeach
</div>
<p class="muted">Montos en USD y bolívares con la tasa vigente ({{ \App\Support\Money::rate() }}). Classic 150, Diamante 150 y Tundra 150 conservan los montos de ejemplo; el resto usa la misma fórmula: saldo ÷ 9.</p>
