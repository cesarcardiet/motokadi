@extends('layouts.app')
@section('title', 'Tasa del dólar')
@section('content')
    <section class="hero">
        <p class="kicker">Administración</p>
        <h1>Tasa del dólar</h1>
        <p class="lead muted">Escribe cuántos bolívares equivalen a <strong>1 dólar</strong>. Si 1.000 Bs = 1 USD, coloca <strong>1000</strong>.</p>

        <div class="panel rate-panel">
            <form method="POST" action="{{ route('admin.exchange-rate.update') }}" class="rate-form">
                @csrf
                @method('PUT')
                <label for="usd_ves_rate">Bolívares = 1 dólar</label>
                <div class="rate-row">
                    <input
                        id="usd_ves_rate"
                        name="usd_ves_rate"
                        type="number"
                        step="0.01"
                        min="0.01"
                        value="{{ old('usd_ves_rate', rtrim(rtrim(number_format($rate, 4, '.', ''), '0'), '.') ?: '0') }}"
                        required
                        data-rate-input
                        aria-describedby="rate-preview"
                    >
                    <span class="rate-suffix">bolívares</span>
                    <button class="btn" type="submit">Guardar tasa</button>
                </div>
                @error('usd_ves_rate')<div class="help">{{ $message }}</div>@enderror
            </form>

            <p class="rate-preview" id="rate-preview" data-rate-preview>
                <strong>{{ \App\Support\Money::plain($rate) }} bolívares equivalen a 1 dólar</strong>
            </p>
        </div>
    </section>

    <script>
        (() => {
            const input = document.querySelector('[data-rate-input]');
            const preview = document.querySelector('[data-rate-preview]');
            if (!input || !preview) return;
            const fmt = (n) => Math.round(Number(n)).toLocaleString('es-VE', { maximumFractionDigits: 0 });
            const paint = () => {
                const rate = Number(input.value);
                if (!Number.isFinite(rate) || rate <= 0) return;
                preview.innerHTML = `<strong>${fmt(rate)} bolívares equivalen a 1 dólar</strong>`;
            };
            input.addEventListener('input', paint);
            paint();
        })();
    </script>
@endsection
