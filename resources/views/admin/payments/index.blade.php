@extends('layouts.app')
@section('title', 'Pagos y comprobantes')
@section('content')
    <section class="hero" data-live-payments="admin">
        <p class="kicker">Cartera</p>
        <h1>Pagos</h1>
        <p class="lead muted">{{ $pendingCount }} comprobante{{ $pendingCount === 1 ? '' : 's' }} esperando revisión. Tasa vigente: {{ \App\Support\Money::rate() }} · <a href="{{ route('admin.exchange-rate.edit') }}">Cambiar tasa</a></p>
        <div class="filters stage-filters">
            <a href="{{ route('admin.payments.index', ['estado' => 'pendiente']) }}" @class(['active' => $status === 'pendiente'])>En revisión</a>
            <a href="{{ route('admin.payments.index', ['estado' => 'aprobado']) }}" @class(['active' => $status === 'aprobado'])>Aprobados</a>
            <a href="{{ route('admin.payments.index', ['estado' => 'rechazado']) }}" @class(['active' => $status === 'rechazado'])>Rechazados</a>
            <a href="{{ route('admin.payments.index', ['estado' => 'todos']) }}" @class(['active' => $status === 'todos'])>Todos</a>
        </div>
        <p class="muted">El cliente solo sube el comprobante. La cuota <strong>no se marca pagada</strong> hasta que un administrador la apruebe aquí.</p>
        <div class="voucher-board">
            @forelse ($vouchers as $voucher)
                <a class="voucher-card voucher-card-hit" href="{{ route('admin.payments.show', $voucher) }}">
                    <div class="voucher-card-top">
                        <div class="voucher-thumb-wrap">
                            @if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $voucher->path))
                                <img class="voucher-thumb" src="{{ asset('storage/'.$voucher->path) }}" alt="Comprobante">
                            @else
                                <div class="voucher-thumb voucher-thumb-file">PDF</div>
                            @endif
                            <div>
                                <p class="kicker">{{ $voucher->status->label() }}</p>
                                <h2>{{ $voucher->client->name }}</h2>
                                <p class="muted">
                                    {{ $voucher->installment->contract->motorcycle->name }} ·
                                    cuota #{{ $voucher->installment->number }} ·
                                    {!! \App\Support\Money::bothLines($voucher->amount, $voucher->frozenRate()) !!}
                                </p>
                                @if ($voucher->frozenRate())
                                    <p class="muted rate-chip">Tasa congelada: {{ $voucher->frozenRateLabel() }} · {{ $voucher->frozenRateWhenLabel() }}</p>
                                @endif
                            </div>
                        </div>
                        <span class="btn small">Validar</span>
                    </div>
                    <div class="voucher-meta">
                        <span>Subido {{ $voucher->created_at->format('d/m/Y H:i') }}</span>
                        <span>{{ $voucher->original_name }}</span>
                        @if ($voucher->reference)<span>Ref. {{ $voucher->reference }}</span>@endif
                    </div>
                </a>
            @empty
                <div class="panel"><p class="muted">No hay comprobantes en este filtro.</p></div>
            @endforelse
        </div>
        {{ $vouchers->links('partials.pager') }}
    </section>
@endsection
