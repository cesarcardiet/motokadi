@extends('layouts.app')
@section('title', 'Simulación de cuotas')
@section('content')
    <section class="section">
        <p class="kicker">Plan a 9 meses</p>
        <h1>Simulación de cuotas</h1>
        <p class="muted">Saldo = precio − inicial. Cuota = saldo ÷ 9, truncada al centavo. Classic queda en $74,44, Diamante en $100,00 y Tundra en $107,77.</p>
        <x-quote-table :motorcycles="$motorcycles" />
        {{ $motorcycles->links('partials.pager') }}
    </section>
@endsection
