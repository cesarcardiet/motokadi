@extends('layouts.app')
@section('title', 'Cartera y contratos')
@section('content')
    <section class="hero">
        <p class="kicker">Administración</p>
        <h1>Cartera</h1>
        <p class="lead muted">{{ $lateCount }} cuota{{ $lateCount === 1 ? '' : 's' }} en mora en el sistema.</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Contrato</th>
                        <th>Cliente</th>
                        <th>Perfil</th>
                        <th>Moto</th>
                        <th>Firmado</th>
                        <th>Cuotas</th>
                        <th>Mora</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contracts as $contract)
                        @php
                            $paid = $contract->installments->where('status', \App\Enums\InstallmentStatus::Paid)->count();
                            $late = $contract->installments->where('status', \App\Enums\InstallmentStatus::Late)->count();
                        @endphp
                        <tr>
                            <td><a href="{{ route('admin.contracts.show', $contract) }}">{{ $contract->contract_number }}</a></td>
                            <td>{{ $contract->client->name }}</td>
                            <td>{{ $contract->client->credit_profile?->label() ?? '—' }}</td>
                            <td>{{ $contract->motorcycle->name }}</td>
                            <td>{{ $contract->signed_at->format('d/m/Y') }}</td>
                            <td>{{ $paid }}/{{ $contract->term_months }} pagadas</td>
                            <td>{{ $late > 0 ? $late.' en mora' : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Aún no hay contratos firmados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $contracts->links('partials.pager') }}
    </section>
@endsection
