@extends('layouts.app')
@section('title', 'Simulación de cuotas')
@section('content')
    <section class="section">
        <p class="kicker">Plan a 9 meses</p>
        <h1>Simulación de cuotas</h1>
        <p class="muted">Saldo = precio − inicial. Cuota = saldo ÷ 9. Tasa vigente: {{ \App\Support\Money::rate() }}. Classic $74 · Diamante $100 · Tundra $108 (más bolívares).</p>
        <x-quote-table :motorcycles="$motorcycles" />
        {{ $motorcycles->links('partials.pager') }}
    </section>
@endsection
