@extends('layouts.app')
@section('title', 'Perfiles de crédito')
@section('content')
    <section class="hero">
        <p class="kicker">Referencias</p>
        <h1>Perfiles de crédito</h1>
        <p class="lead muted">Tres clientes de referencia para comparar buen pagador, intermedio y mal historial.</p>
        <div class="profile-grid">
            @foreach ($profiles as $client)
                @php($contract = $client->contracts->first())
                <article class="profile-card profile-{{ $client->credit_profile?->tone() }}">
                    <p class="kicker">{{ $client->credit_profile?->label() }}</p>
                    <h2>{{ $client->name }}</h2>
                    <p class="muted">{{ $client->credit_profile?->description() }}</p>
                    <ul class="profile-meta">
                        <li><strong>Cédula</strong><span>{{ $client->national_id }}</span></li>
                        <li><strong>WhatsApp</strong><span>{{ $client->phone }}</span></li>
                        <li><strong>Correo</strong><span>{{ $client->email }}</span></li>
                        @if ($contract)
                            <li><strong>Contrato</strong><span><a href="{{ route('admin.contracts.show', $contract) }}">{{ $contract->contract_number }}</a></span></li>
                            <li><strong>Moto</strong><span>{{ $contract->motorcycle->name }}</span></li>
                            <li><strong>Pagadas</strong><span>{{ $contract->installments->where('status', \App\Enums\InstallmentStatus::Paid)->count() }}/{{ $contract->term_months }}</span></li>
                            <li><strong>En mora</strong><span>{{ $contract->installments->where('status', \App\Enums\InstallmentStatus::Late)->count() }}</span></li>
                        @endif
                    </ul>
                </article>
            @endforeach
        </div>
    </section>
@endsection
