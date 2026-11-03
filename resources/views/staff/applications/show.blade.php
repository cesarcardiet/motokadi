@extends('layouts.app')
@section('title', 'Revisar solicitud')
@section('content')
    @php
        $docs = $application->documents;
        $pendingDocs = $docs->filter(fn ($doc) => $doc->status !== \App\Enums\DocumentStatus::Accepted);
        $acceptedCount = $docs->filter(fn ($doc) => $doc->status === \App\Enums\DocumentStatus::Accepted)->count();
        $totalDocs = $docs->count();
        $allDocsAccepted = $totalDocs > 0 && $pendingDocs->isEmpty();
        $canReviewDocs = in_array($application->status, [
            \App\Enums\ApplicationStatus::Preevaluation,
            \App\Enums\ApplicationStatus::Papers,
        ], true);
    @endphp
    <section class="hero">
        <p class="kicker">{{ $application->branch->name }}</p>
        <h1>{{ $application->client->name }}</h1>
        <p class="lead">{{ $application->motorcycle->name }} · {{ $application->color }} · {{ $application->channel->label() }}</p>
        <p>
            Dirección declarada: {{ $application->address }}, {{ $application->city }}
            @if ($application->latitude && $application->longitude)
                · GPS ref. {{ $application->latitude }}, {{ $application->longitude }}
            @endif
        </p>
        <x-status-stepper :application="$application" />
        <div class="quote-box dark-stats">
            <div><span class="muted">Precio</span><strong>{!! \App\Support\Money::bothLines($application->price) !!}</strong></div>
            <div><span class="muted">Inicial</span><strong>{!! \App\Support\Money::bothLines($application->initial_payment) !!}</strong></div>
            <div><span class="muted">Saldo</span><strong>{!! \App\Support\Money::bothLines($application->balance) !!}</strong></div>
            <div><span class="muted">Cuota</span><strong>{!! \App\Support\Money::bothLines($application->monthly_payment) !!}</strong></div>
        </div>

        <div class="panel">
            <div class="split">
                <div>
                    <h2>Papeles</h2>
                    <p class="muted">
                        {{ $acceptedCount }}/{{ max($totalDocs, 1) }} aceptados.
                        @if ($pendingDocs->isNotEmpty())
                            Faltan: {{ $pendingDocs->map(fn ($d) => $d->type->label())->join(', ') }}.
                        @else
                            Todos los recaudos están aceptados.
                        @endif
                    </p>
                </div>
            </div>

            @foreach ($docs as $document)
                <div id="doc-{{ $document->id }}" class="doc-row @if($document->status === \App\Enums\DocumentStatus::Accepted) doc-row-ok @elseif($document->status === \App\Enums\DocumentStatus::Rejected) doc-row-bad @else doc-row-pending @endif">
                    <div>
                        <strong>{{ $document->type->label() }}</strong>
                        <div>
                            @if ($document->status === \App\Enums\DocumentStatus::Accepted)
                                <span class="pill pill-ok">Aprobado</span>
                            @elseif ($document->status === \App\Enums\DocumentStatus::Rejected)
                                <span class="pill pill-bad">Rechazado</span>
                            @else
                                <span class="pill pill-warn">Pendiente de aprobar</span>
                            @endif
                            @if ($document->review_note)
                                <span class="muted"> · {{ $document->review_note }}</span>
                            @endif
                        </div>
                        @if ($document->path)
                            <a href="{{ asset('storage/'.$document->path) }}" target="_blank" rel="noopener">Abrir archivo</a>
                        @else
                            <span class="muted">Entregado en la sede</span>
                        @endif
                    </div>
                    @if ($canReviewDocs)
                        <form method="POST" action="{{ route('staff.documents.review', [$application, $document]) }}" class="doc-review-form" data-keep-view="doc-{{ $document->id }}">
                            @csrf
                            <select name="status">
                                <option value="aceptado" @selected($document->status === \App\Enums\DocumentStatus::Accepted)>Aceptar</option>
                                <option value="rechazado" @selected($document->status === \App\Enums\DocumentStatus::Rejected)>Rechazar</option>
                            </select>
                            <input name="review_note" placeholder="Nota opcional" value="{{ $document->review_note }}">
                            <button class="btn small" type="submit">
                                {{ $document->status === \App\Enums\DocumentStatus::Accepted ? 'Actualizar' : 'Aprobar / guardar' }}
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($application->status === \App\Enums\ApplicationStatus::Preevaluation && auth()->user()->hasPermission('solicitudes.preevaluar'))
            <form class="panel" method="POST" action="{{ route('staff.applications.preevaluate', $application) }}">
                @csrf
                <h2>Cerrar preevaluación</h2>
                <p class="muted">Solo cuando los papeles en línea estén en orden. Esto envía el expediente a verificación en sede.</p>
                <button class="btn" type="submit" @disabled(! $allDocsAccepted)>Aprobar preevaluación</button>
                @unless ($allDocsAccepted)
                    <p class="help">Primero aprueba todos los documentos pendientes arriba.</p>
                @endunless
            </form>
        @endif

        @if ($application->status === \App\Enums\ApplicationStatus::Papers && auth()->user()->hasPermission('solicitudes.verificar_papeles'))
            @if ($application->documents->isEmpty())
                <form class="panel" method="POST" action="{{ route('staff.applications.folder', $application) }}">
                    @csrf
                    <h2>Carpeta en mano</h2>
                    <p class="muted">El cliente trajo cédula, RIF, licencia con 2DA y constancia. Al confirmar, quedan aceptados y sigue la visita a domicilio.</p>
                    <button class="btn" type="submit">Recibí la carpeta completa</button>
                </form>
            @else
                <div class="panel">
                    <h2>Cerrar verificación de papeles</h2>
                    @if ($allDocsAccepted)
                        <p class="muted">Todos los recaudos están <strong>aprobados</strong>. El siguiente paso es la visita / prueba de vista en el domicilio (antes de entregar la moto).</p>
                        <form method="POST" action="{{ route('staff.applications.papers', $application) }}">
                            @csrf
                            <button class="btn" type="submit">Papeles listos → pasar a visita</button>
                        </form>
                    @else
                        <div class="flash error">
                            Aún no puedes pasar a la visita. Aprueba cada papel pendiente con el botón
                            <strong>Aprobar / guardar</strong>. Faltan:
                            {{ $pendingDocs->map(fn ($d) => $d->type->label())->join(', ') }}.
                        </div>
                    @endif
                </div>
            @endif
        @endif

        @if ($application->status === \App\Enums\ApplicationStatus::Location && auth()->user()->hasPermission('solicitudes.verificar_ubicacion'))
            <form class="panel" method="POST" action="{{ route('staff.applications.location', $application) }}">
                @csrf
                <h2>Visita a domicilio / prueba de vista</h2>
                <p class="muted">
                    Este paso <strong>no se aprueba desde el escritorio</strong>. Un visitante va al domicilio,
                    confirma que el solicitante vive ahí, y luego registras el resultado aquí.
                    Es la verificación previa a la entrega de la moto.
                </p>
                <div class="docs-note" style="margin-bottom:14px">
                    <strong>Dirección a visitar</strong>
                    <p class="muted" style="margin:0">{{ $application->address }}, {{ $application->city }}</p>
                    @if ($application->whatsapp_phone)
                        <p class="muted" style="margin:6px 0 0">WhatsApp: {{ $application->whatsapp_phone }}</p>
                    @endif
                </div>
                <div class="form-grid">
                    <div>
                        <label for="latitude">Latitud (ref. GPS)</label>
                        <input id="latitude" name="latitude" value="{{ old('latitude', $application->latitude) }}" required>
                    </div>
                    <div>
                        <label for="longitude">Longitud (ref. GPS)</label>
                        <input id="longitude" name="longitude" value="{{ old('longitude', $application->longitude) }}" required>
                    </div>
                    <div class="full">
                        <label for="note">Resultado de la visita</label>
                        <textarea id="note" name="note" required placeholder="Ej. Visité el domicilio; confirmo que vive en esta dirección.">{{ old('note') }}</textarea>
                        @error('note')<div class="help">{{ $message }}</div>@enderror
                    </div>
                    <label class="check full">
                        <input type="checkbox" name="confirm" value="1" @checked(old('confirm'))>
                        Confirmo que la visita se realizó y la persona vive en esta dirección
                    </label>
                    @error('confirm')<div class="help">{{ $message }}</div>@enderror
                </div>
                <button class="btn" type="submit">Visita OK — preaprobar</button>
            </form>
        @endif

        @if ($application->status === \App\Enums\ApplicationStatus::Preapproved && auth()->user()->hasPermission('solicitudes.aprobar'))
            <form class="panel" method="POST" action="{{ route('staff.applications.approve', $application) }}">
                @csrf
                <h2>Aprobar y asignar moto</h2>
                <p class="muted">Papeles y visita listos. Al aprobar se firma el contrato y se asigna la unidad física que se entrega al cliente. Antes de este paso no hay cartel, placa ni serial.</p>
                <p class="muted"><strong>{{ $application->motorcycle->name }}</strong> · color {{ $application->color }} · sede {{ $application->branch->name }}</p>
                <div class="grid-3">
                    <div>
                        <label for="unit_cartel">Nº de cartel</label>
                        <input id="unit_cartel" type="text" name="unit_cartel" value="{{ old('unit_cartel') }}" required maxlength="80" placeholder="Ej. CT-10482" autocomplete="off">
                        @error('unit_cartel')<div class="help">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="unit_plate">Nº de placa</label>
                        <input id="unit_plate" type="text" name="unit_plate" value="{{ old('unit_plate') }}" required maxlength="80" placeholder="Ej. AB123CD" autocomplete="off">
                        @error('unit_plate')<div class="help">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="unit_serial">Nº de serial</label>
                        <input id="unit_serial" type="text" name="unit_serial" value="{{ old('unit_serial') }}" required maxlength="120" placeholder="Ej. VIN / chasis" autocomplete="off">
                        @error('unit_serial')<div class="help">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button class="btn" type="submit">Aprobar, asignar y generar contrato</button>
            </form>
        @endif

        @if ($application->status === \App\Enums\ApplicationStatus::Approved && $application->contract)
            <div class="panel">
                <h2>Contrato firmado</h2>
                <p>
                    {{ $application->contract->contract_number }} ·
                    firmado {{ $application->contract->signed_at->format('d/m/Y') }} ·
                    {{ $application->contract->installments->where('status', \App\Enums\InstallmentStatus::Paid)->count() }}/{{ $application->contract->term_months }} cuotas pagadas
                    @php($late = $application->contract->installments->where('status', \App\Enums\InstallmentStatus::Late)->count())
                    @if ($late > 0)
                        · <strong>{{ $late }} en mora</strong>
                    @endif
                </p>
                <x-assigned-moto :contract="$application->contract" />
                @if (auth()->user()->hasPermission('panel.admin'))
                    <a class="btn small" href="{{ route('admin.contracts.show', $application->contract) }}">Ver cuotas</a>
                @endif
            </div>
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
