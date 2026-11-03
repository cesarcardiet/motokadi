@extends('layouts.app')
@section('title', 'Comprobante #'.$voucher->id)
@section('content')
    <section class="hero">
        <p class="kicker">Revisión de pago</p>
        <h1>{{ $voucher->client->name }}</h1>
        <p class="lead">
            {{ $voucher->installment->contract->motorcycle->name }} ·
            cuota #{{ $voucher->installment->number }} ·
            {!! \App\Support\Money::bothLines($voucher->amount, $voucher->frozenRate()) !!}
        </p>
        <div class="filters stage-filters">
            <a href="{{ route('admin.payments.index') }}">← Volver a pagos</a>
            <a href="{{ route('admin.contracts.show', $voucher->installment->contract) }}">Ver contrato</a>
        </div>

        <div class="payment-review-grid">
            <div class="panel">
                <h2>Comprobante</h2>
                <p class="muted">{{ $voucher->original_name }} · subido {{ $voucher->created_at->format('d/m/Y H:i') }}</p>
                @if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $voucher->path))
                    <img class="voucher-preview" src="{{ asset('storage/'.$voucher->path) }}" alt="Comprobante">
                @else
                    <a class="btn" href="{{ asset('storage/'.$voucher->path) }}" target="_blank" rel="noopener">Abrir PDF / archivo</a>
                @endif
            </div>
            <div class="panel">
                <h2>Datos</h2>
                <ul class="profile-meta">
                    <li><strong>Estado</strong><span>{{ $voucher->status->label() }}</span></li>
                    <li><strong>Monto USD</strong><span>{{ \App\Support\Money::usd($voucher->amount) }}</span></li>
                    <li><strong>Monto Bs</strong><span>{{ \App\Support\Money::ves($voucher->amountInVes()) }}</span></li>
                    <li>
                        <strong>Tasa congelada</strong>
                        <span>
                            {{ $voucher->frozenRateLabel() }}
                            <small class="muted"> · al {{ $voucher->frozenRateWhenLabel() }}</small>
                        </span>
                    </li>
                    <li><strong>Tasa vigente hoy</strong><span>{{ \App\Support\Money::rate() }}</span></li>
                    <li><strong>Fecha pago</strong><span>{{ $voucher->paid_on?->format('d/m/Y') ?? '—' }}</span></li>
                    <li><strong>Referencia</strong><span>{{ $voucher->reference ?: '—' }}</span></li>
                    <li><strong>WhatsApp</strong><span>{{ $voucher->client->phone ?: '—' }}</span></li>
                    <li><strong>Vence cuota</strong><span>{{ $voucher->installment->due_date->format('d/m/Y') }}</span></li>
                </ul>

                @if ($voucher->status === \App\Enums\VoucherStatus::Pending)
                    <form method="POST" action="{{ route('admin.payments.approve', $voucher) }}" class="review-actions">
                        @csrf
                        <label for="approve_note">Nota (opcional)</label>
                        <input id="approve_note" name="review_note" maxlength="180" placeholder="Ej. Transferencia verificada">
                        <button class="btn" type="submit">Aprobar pago</button>
                    </form>
                    <form method="POST" action="{{ route('admin.payments.reject', $voucher) }}" class="review-actions">
                        @csrf
                        <label for="reject_note">Motivo del rechazo</label>
                        <input id="reject_note" name="review_note" required minlength="8" maxlength="180" placeholder="Ej. Monto no coincide">
                        @error('review_note')<div class="help">{{ $message }}</div>@enderror
                        <button class="btn bad" type="submit">Rechazar</button>
                    </form>
                @else
                    <p class="muted">
                        Revisado {{ $voucher->reviewed_at?->format('d/m/Y H:i') }}
                        @if ($voucher->reviewer) por {{ $voucher->reviewer->name }} @endif
                        @if ($voucher->review_note) · {{ $voucher->review_note }} @endif
                    </p>
                @endif
            </div>
        </div>
    </section>
@endsection
