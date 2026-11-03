@extends('layouts.app')
@section('title', 'Revisar solicitud')
@section('content')
    <section class="hero">
        <p class="kicker">{{ $application->branch->name }}</p>
        <h1>{{ $application->client->name }}</h1>
        <p class="lead">{{ $application->motorcycle->name }} · {{ $application->color }} · {{ $application->channel->label() }}</p>
        <p>{{ $application->address }}, {{ $application->city }}
            @if ($application->latitude && $application->longitude)
                · GPS {{ $application->latitude }}, {{ $application->longitude }}
            @elseif ($application->status === \App\Enums\ApplicationStatus::Location)
                · Falta registrar el GPS para preaprobar.
            @else
                · La ubicación se confirma cuando llegan los papeles.
            @endif
        </p>
        <x-status-stepper :application="$application" />
        <div class="quote-box">
            <div><span class="muted">Precio</span><strong>{{ \App\Support\Money::usd($application->price) }}</strong></div>
            <div><span class="muted">Inicial</span><strong>{{ \App\Support\Money::usd($application->initial_payment) }}</strong></div>
            <div><span class="muted">Saldo</span><strong>{{ \App\Support\Money::usd($application->balance) }}</strong></div>
            <div><span class="muted">Cuota</span><strong>{{ \App\Support\Money::usd($application->monthly_payment) }}</strong></div>
        </div>

        <div class="panel">
            <h2>Papeles</h2>
            @foreach ($application->documents as $document)
                <div class="doc-row">
                    <div>
                        <strong>{{ $document->type->label() }}</strong>
                        <div class="muted">{{ $document->status->label() }}</div>
                        @if ($document->path)
                            <a href="{{ asset('storage/'.$document->path) }}" target="_blank" rel="noopener">Abrir archivo</a>
                        @else
                            <span class="muted">Entregado en la sede</span>
                        @endif
                    </div>
                    @if (in_array($application->status, [\App\Enums\ApplicationStatus::Preevaluation, \App\Enums\ApplicationStatus::Papers], true))
                        <form method="POST" action="{{ route('staff.documents.review', [$application, $document]) }}">
                            @csrf
                            <select name="status">
                                <option value="aceptado">Aceptar</option>
                                <option value="rechazado">Rechazar</option>
                            </select>
                            <input name="review_note" placeholder="Nota opcional">
                            <button class="btn small" type="submit">Guardar</button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($application->status === \App\Enums\ApplicationStatus::Preevaluation && auth()->user()->hasPermission('solicitudes.preevaluar'))
            <form method="POST" action="{{ route('staff.applications.preevaluate', $application) }}">
                @csrf
                <button class="btn" type="submit">Aprobar preevaluación y enviar a sede</button>
            </form>
        @endif

        @if ($application->status === \App\Enums\ApplicationStatus::Papers && auth()->user()->hasPermission('solicitudes.verificar_papeles'))
            @if ($application->documents->isEmpty())
                <form class="panel" method="POST" action="{{ route('staff.applications.folder', $application) }}">
                    @csrf
                    <h2>Carpeta en mano</h2>
                    <p class="muted">El cliente trajo cédula, RIF, licencia con 2DA y constancia de trabajo. Al confirmar, esos cuatro recaudos quedan aceptados y la solicitud pasa a ubicación.</p>
                    <button class="btn" type="submit">Recibí la carpeta completa</button>
                </form>
            @else
                <form method="POST" action="{{ route('staff.applications.papers', $application) }}">
                    @csrf
                    <button class="btn" type="submit">Papeles verificados, pasar a ubicación</button>
                </form>
            @endif
        @endif

        @if ($application->status === \App\Enums\ApplicationStatus::Location && auth()->user()->hasPermission('solicitudes.verificar_ubicacion'))
            <form class="panel" method="POST" action="{{ route('staff.applications.location', $application) }}">
                @csrf
                <h2>Verificación de ubicación</h2>
                <p class="muted">Si la dirección y el GPS coinciden, la solicitud queda preaprobada.</p>
                <div class="form-grid">
                    <div>
                        <label for="latitude">Latitud</label>
                        <input id="latitude" name="latitude" value="{{ old('latitude', $application->latitude) }}" required>
                    </div>
                    <div>
                        <label for="longitude">Longitud</label>
                        <input id="longitude" name="longitude" value="{{ old('longitude', $application->longitude) }}" required>
                    </div>
                    <div class="full">
                        <label for="note">Cómo se verificó</label>
                        <textarea id="note" name="note" required>{{ old('note') }}</textarea>
                        @error('note')<div class="help">{{ $message }}</div>@enderror
                    </div>
                    <label class="check full"><input type="checkbox" name="confirm" value="1"> Confirmo que la ubicación coincide con el solicitante</label>
                    @error('confirm')<div class="help">{{ $message }}</div>@enderror
                </div>
                <button class="btn" type="submit">Verificar ubicación y preaprobar</button>
            </form>
        @endif

        @if ($application->status === \App\Enums\ApplicationStatus::Preapproved && auth()->user()->hasPermission('solicitudes.aprobar'))
            <form method="POST" action="{{ route('staff.applications.approve', $application) }}">
                @csrf
                <button class="btn" type="submit">Dar aprobación final</button>
            </form>
        @endif

        @if ($application->status->isOpen() && auth()->user()->hasPermission('solicitudes.rechazar'))
            <form class="panel" method="POST" action="{{ route('staff.applications.reject', $application) }}">
                @csrf
                <h2>Rechazar</h2>
                <textarea name="reason" required placeholder="Motivo del rechazo"></textarea>
                @error('reason')<div class="help">{{ $message }}</div>@enderror
                <div class="actions"><button class="btn bad" type="submit">Rechazar solicitud</button></div>
            </form>
        @endif

        <div class="panel">
            <h2>Historial</h2>
            @foreach ($application->events as $event)
                <p><strong>{{ $event->created_at->format('d/m/Y H:i') }}</strong> · {{ $event->actor?->name }}<br>{{ $event->note }}</p>
            @endforeach
        </div>
    </section>
@endsection
