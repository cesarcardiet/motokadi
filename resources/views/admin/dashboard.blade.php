@extends('layouts.app')
@section('title', 'Administración')
@section('content')
    <section class="hero">
        <p class="kicker">Panel</p>
        <h1>Administración</h1>
        <div class="stat-row dark-stats">
            <div class="stat"><span class="muted">Solicitudes activas</span><strong><a href="{{ route('staff.applications.index', ['estado' => 'activas']) }}">{{ $activeCount }}</a></strong></div>
            <div class="stat"><span class="muted">Pagos por validar</span><strong><a href="{{ route('admin.payments.index', ['estado' => 'pendiente']) }}">{{ $pendingPayments }}</a></strong></div>
            <div class="stat"><span class="muted">Modelos activos</span><strong>{{ $motorcycles }}</strong></div>
            <div class="stat"><span class="muted">Sedes</span><strong>{{ $branches }}</strong></div>
            <div class="stat"><span class="muted">Vendedores</span><strong>{{ $sellers }}</strong></div>
            <div class="stat"><span class="muted">Tasa USD/Bs</span><strong><a href="{{ route('admin.exchange-rate.edit') }}">{{ \App\Support\Money::rate() }}</a></strong></div>
        </div>
        <div class="stat-row dark-stats" style="margin-top:12px">
            @foreach ($statuses as $status)
                <div class="stat"><span class="muted">{{ $status->label() }}</span><strong>{{ $counts[$status->value] ?? 0 }}</strong></div>
            @endforeach
        </div>
        @if ($recentVouchers->isNotEmpty())
            <h2 style="margin-top:28px">Comprobantes por validar</h2>
            <div class="voucher-board" style="margin-bottom:18px">
                @foreach ($recentVouchers as $voucher)
                    <a class="voucher-card voucher-card-hit" href="{{ route('admin.payments.show', $voucher) }}">
                        <div class="voucher-card-top">
                            <div>
                                <p class="kicker">En revisión</p>
                                <h2>{{ $voucher->client->name }}</h2>
                                <p class="muted">
                                    {{ $voucher->installment->contract->motorcycle->name }} ·
                                    cuota #{{ $voucher->installment->number }} ·
                                    {!! \App\Support\Money::bothLines($voucher->amount, $voucher->frozenRate()) !!}
                                </p>
                                @if ($voucher->frozenRate())
                                    <p class="muted rate-chip">Tasa congelada: {{ $voucher->frozenRateLabel() }}</p>
                                @endif
                            </div>
                            <span class="btn small">Validar</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif

        <h2 style="margin-top:28px">Últimas solicitudes</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Cliente</th><th>Moto</th><th>Sede</th><th>Estado</th></tr></thead>
                <tbody>
                    @forelse ($recent as $application)
                        <tr>
                            <td><a href="{{ route('staff.applications.show', $application) }}">{{ $application->client->name }}</a></td>
                            <td>{{ $application->motorcycle->name }}</td>
                            <td>{{ $application->branch->city }}</td>
                            <td>{{ $application->status->label() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Aún no hay solicitudes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
