@extends('layouts.app')
@section('title', 'Mis comprobantes')
@section('content')
    <section class="hero">
        <p class="kicker">Usuario</p>
        <h1>Mis comprobantes</h1>
        <p class="lead muted">Descarga el voucher PDF de cada pago: monto, fecha, referencia, contrato y moto asignada.</p>
        <div class="filters">
            <a href="{{ route('client.payments.index') }}">← Mis pagos</a>
        </div>

        @forelse ($vouchers as $voucher)
            @php
                $contract = $voucher->installment->contract;
                $moto = $contract->motorcycle;
            @endphp
            <div class="panel voucher-list-card">
                <div class="split">
                    <div>
                        <h2>Cuota #{{ $voucher->installment->number }} · {{ $moto?->name }}</h2>
                        <p class="muted">{{ $contract->contract_number }} · {{ $voucher->paid_on?->format('d/m/Y') ?? $voucher->created_at->format('d/m/Y') }}</p>
                        <p>
                            <span @class([
                                'pill',
                                'pill-ok' => $voucher->status === \App\Enums\VoucherStatus::Approved,
                                'pill-warn' => $voucher->status === \App\Enums\VoucherStatus::Pending,
                                'pill-bad' => $voucher->status === \App\Enums\VoucherStatus::Rejected,
                            ])>{{ $voucher->status->label() }}</span>
                        </p>
                        <div class="quote-box dark-stats" style="margin-top:12px">
                            <div><span class="muted">Monto</span><strong>{!! \App\Support\Money::bothLines($voucher->amount, $voucher->frozenRate()) !!}</strong></div>
                            <div><span class="muted">Referencia</span><strong>{{ $voucher->reference ?: '—' }}</strong></div>
                            @if ($contract->hasAssignedUnit())
                                <div><span class="muted">Placa</span><strong>{{ $contract->unit_plate }}</strong></div>
                                <div><span class="muted">Cartel</span><strong>{{ $contract->unit_cartel }}</strong></div>
                            @endif
                        </div>
                    </div>
                    <div class="voucher-actions">
                        <a class="btn" href="{{ route('client.payments.voucher.pdf', $voucher) }}">Descargar PDF</a>
                        @if ($voucher->path)
                            <a class="btn small ghost" href="{{ asset('storage/'.$voucher->path) }}" target="_blank" rel="noopener">Ver archivo subido</a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="panel">
                <p class="muted">Aún no has enviado comprobantes. Cuando subas un pago desde Mis pagos, el voucher PDF aparecerá aquí.</p>
            </div>
        @endforelse
    </section>
@endsection
