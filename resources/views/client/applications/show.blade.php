@extends('layouts.app')
@section('title', 'Solicitud '.$application->id)
@section('content')
    <section class="hero">
        <p class="kicker">{{ $application->channel->label() }}</p>
        <h1>{{ $application->motorcycle->name }}</h1>
        <p class="lead">{{ $application->color }} · {{ $application->branch->name }} · {{ $application->address }}, {{ $application->city }}@if($application->whatsapp_phone) · WhatsApp {{ $application->whatsapp_phone }}@endif</p>
        <x-status-stepper :application="$application" />
        @if ($application->contract)
            <x-assigned-moto :contract="$application->contract" />
        @endif
        <div class="quote-box dark-stats">
            <div><span class="muted">Precio</span><strong>{!! \App\Support\Money::bothLines($application->price) !!}</strong></div>
            <div><span class="muted">Inicial</span><strong>{!! \App\Support\Money::bothLines($application->initial_payment) !!}</strong></div>
            <div><span class="muted">Saldo</span><strong>{!! \App\Support\Money::bothLines($application->balance) !!}</strong></div>
            <div><span class="muted">Cuota</span><strong>{!! \App\Support\Money::bothLines($application->monthly_payment) !!}</strong></div>
        </div>
        <div class="panel">
            <h2>Documentos</h2>
            @if ($application->documents->isEmpty())
                <p class="muted">Todavía no hay archivos cargados. Lleva cédula, RIF, licencia con 2DA y constancia de trabajo a la sede.</p>
            @endif
            @foreach ($application->documents as $document)
                <div class="doc-row">
                    <div>
                        <strong>{{ $document->type->label() }}</strong>
                        <div class="muted">{{ $document->status->label() }} @if($document->review_note) · {{ $document->review_note }} @endif</div>
                        @if ($document->path)
                            <a href="{{ asset('storage/'.$document->path) }}" target="_blank" rel="noopener">Ver archivo</a>
                        @else
                            <span class="muted">Lo llevas en la carpeta a la sede</span>
                        @endif
                    </div>
                    @if ($document->status === \App\Enums\DocumentStatus::Rejected)
                        <form method="POST" action="{{ route('client.documents.replace', [$application, $document]) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="file" required>
                            <button class="btn small" type="submit">Reemplazar</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="panel">
            <h2>Historial</h2>
            @foreach ($application->events as $event)
                <p><strong>{{ $event->created_at->format('d/m/Y H:i') }}</strong> · {{ $event->actor?->name ?? 'Sistema' }}<br>{{ $event->note }}</p>
            @endforeach
        </div>
    </section>
@endsection
