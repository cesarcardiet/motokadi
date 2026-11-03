@extends('layouts.app')
@section('title', $contract->contract_number)
@section('content')
    <section class="hero">
        <p class="kicker">Contrato</p>
        <h1>{{ $contract->contract_number }}</h1>
        <p class="lead">{{ $contract->client->name }} · {{ $contract->motorcycle->name }} · {{ $contract->status->label() }}</p>
        <p class="muted">
            Firmado {{ $contract->signed_at->format('d/m/Y') }} ·
            Cuota {!! \App\Support\Money::bothLines($contract->monthly_payment) !!} ·
            Tasa {{ \App\Support\Money::rate() }} ·
            Perfil {{ $contract->client->credit_profile?->label() ?? 'sin clasificar' }}
        </p>
        <x-assigned-moto :contract="$contract" />
        <div class="filters">
            <a href="{{ route('admin.contracts.index') }}">← Cartera</a>
            <a href="{{ route('staff.applications.show', $contract->application) }}">Ver solicitud</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>#</th><th>Vence</th><th>Monto</th><th>Estado</th><th>Pagada</th><th>Días mora</th><th>Recargo</th></tr>
                </thead>
                <tbody>
                    @foreach ($contract->installments as $row)
                        <tr @class(['row-late' => $row->status === \App\Enums\InstallmentStatus::Late])>
                            <td>{{ $row->number }}</td>
                            <td>{{ $row->due_date->format('d/m/Y') }}</td>
                            <td>{!! \App\Support\Money::bothLines($row->amount) !!}</td>
                            <td>{{ $row->status->label() }}</td>
                            <td>{{ $row->paid_at?->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ $row->days_late ?: '—' }}</td>
                            <td>{{ $row->late_fee > 0 ? \App\Support\Money::both($row->late_fee) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
