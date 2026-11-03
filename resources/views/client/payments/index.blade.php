@extends('layouts.app')
@section('title', 'Mis pagos')
@section('content')
    <section class="hero" data-live-payments="client">
        <p class="kicker">Usuario</p>
        <h1>Mis pagos</h1>
        <p class="lead muted">Sube el comprobante de la cuota. Queda <strong>en revisión</strong>: solo el administrador puede aprobar o rechazar el pago. La cuota no se marca pagada sola. Si el admin aprueba estando aquí, esta lista se actualiza sola.</p>
        <div class="filters">
            <a class="btn small" href="{{ route('client.payments.vouchers') }}">Ver / descargar vouchers PDF</a>
        </div>

        @forelse ($contracts as $contract)
            <div class="panel payment-card">
                <x-assigned-moto :contract="$contract" :compact="true" />
                <div class="split">
                    <div>
                        <h2>{{ $contract->motorcycle->name }}</h2>
                        <p class="muted">{{ $contract->contract_number }} · cuota {!! \App\Support\Money::bothLines($contract->monthly_payment) !!}</p>
                        <p class="muted rate-chip">Tasa vigente: {{ \App\Support\Money::rate() }}</p>
                    </div>
                </div>
                <div class="table-wrap nested-table">
                    <table class="stack-table">
                        <thead>
                            <tr><th>#</th><th>Vence</th><th>Monto</th><th>Estado</th><th>Comprobante</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($contract->installments as $row)
                                @php
                                    $pending = $row->vouchers->firstWhere('status', \App\Enums\VoucherStatus::Pending);
                                    $latest = $row->vouchers->first();
                                    $canPay = in_array($row->status, [\App\Enums\InstallmentStatus::Pending, \App\Enums\InstallmentStatus::Late], true) && ! $pending;
                                @endphp
                                <tr @class(['row-late' => $row->status === \App\Enums\InstallmentStatus::Late])>
                                    <td data-label="Cuota">{{ $row->number }}</td>
                                    <td data-label="Vence">{{ $row->due_date->format('d/m/Y') }}</td>
                                    <td data-label="Monto">
                                        @php $snap = $pending ?? ($row->status === \App\Enums\InstallmentStatus::Paid ? $latest : null); @endphp
                                        @if ($snap?->frozenRate())
                                            {!! \App\Support\Money::bothLines($row->amount, $snap->frozenRate()) !!}
                                            <div class="muted rate-chip">Tasa del pago: {{ $snap->frozenRateLabel() }}</div>
                                        @else
                                            {!! \App\Support\Money::bothLines($row->amount) !!}
                                        @endif
                                    </td>
                                    <td data-label="Estado">{{ $row->status->label() }}</td>
                                    <td data-label="Comprobante">
                                        @if ($pending)
                                            <span class="pill pill-warn">En revisión (admin)</span>
                                            <div class="muted">Esperando validación del administrador.</div>
                                            @if ($pending->frozenRate())
                                                <div class="muted">Tasa congelada: {{ $pending->frozenRateLabel() }} · {{ $pending->frozenRateWhenLabel() }}</div>
                                            @endif
                                            <div><a href="{{ route('client.payments.voucher.pdf', $pending) }}">Descargar voucher PDF</a></div>
                                        @elseif ($row->status === \App\Enums\InstallmentStatus::Paid)
                                            <span class="pill pill-ok">Pagada / aprobada</span>
                                            @if ($latest?->frozenRate())
                                                <div class="muted">Tasa del pago: {{ $latest->frozenRateLabel() }} · {{ $latest->frozenRateWhenLabel() }}</div>
                                            @endif
                                            @if ($latest)
                                                <div><a href="{{ route('client.payments.voucher.pdf', $latest) }}">Descargar voucher PDF</a></div>
                                            @endif
                                        @elseif ($latest?->status === \App\Enums\VoucherStatus::Rejected)
                                            <span class="pill pill-bad">Rechazado</span>
                                            <div class="muted">{{ $latest->review_note }} — puedes subir otro comprobante.</div>
                                            <div><a href="{{ route('client.payments.voucher.pdf', $latest) }}">Descargar voucher PDF</a></div>
                                        @endif

                                        @if ($canPay)
                                            <form class="voucher-form" method="POST" action="{{ route('client.payments.store', $row) }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="file" name="voucher" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                                                <input type="date" name="paid_on" max="{{ now()->toDateString() }}" value="{{ now()->toDateString() }}">
                                                <input type="text" name="reference" placeholder="Ref. transferencia (opcional)" maxlength="80">
                                                <button class="btn small" type="submit">Subir para validación</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="panel">
                <p class="muted">Todavía no tienes un contrato activo. Cuando aprueben tu solicitud aparecerán aquí las cuotas.</p>
            </div>
        @endforelse
    </section>
@endsection
