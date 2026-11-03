@props(['motorcycles'])
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Modelo de moto</th>
                <th>Precio estimado</th>
                <th>Tu cuota inicial</th>
                <th>Saldo a financiar</th>
                <th>Tus cuotas mensuales (por {{ $motorcycles->first()?->term_months ?? 9 }} meses)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($motorcycles as $motorcycle)
                @php($quote = $motorcycle->quote())
                <tr>
                    <td>
                        <a href="{{ route('catalog.show', $motorcycle) }}"><strong>{{ $motorcycle->name }}</strong></a>
                        <div class="muted nowrap">{{ $motorcycle->lineLabel() }} · {{ $motorcycle->displacement }} cc</div>
                    </td>
                    <td>{{ \App\Support\Money::usd($quote['price']) }}</td>
                    <td>{{ \App\Support\Money::usd($quote['initial']) }}</td>
                    <td>{{ \App\Support\Money::usd($quote['balance']) }}</td>
                    <td><strong>{{ \App\Support\Money::usd($quote['monthly']) }} al mes</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<p class="muted">Classic 150, Diamante 150 y Tundra 150 conservan los montos de la tabla de ejemplo. En el resto, el precio es una referencia y la cuota sale de la misma cuenta: saldo dividido entre 9, sin subir el centavo.</p>
