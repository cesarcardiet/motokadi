@props(['application'])
@php
    $steps = [
        \App\Enums\ApplicationStatus::Preevaluation,
        \App\Enums\ApplicationStatus::Papers,
        \App\Enums\ApplicationStatus::Location,
        \App\Enums\ApplicationStatus::Preapproved,
        \App\Enums\ApplicationStatus::Approved,
    ];
    $current = $application->status;
    $order = array_map(fn ($step) => $step->value, $steps);
    $position = array_search($current->value, $order, true);

    $actionHint = match ($current) {
        \App\Enums\ApplicationStatus::Preevaluation => 'Ahora: aceptar o rechazar cada documento y luego aprobar la preevaluación.',
        \App\Enums\ApplicationStatus::Papers => 'Ahora: marca cada papel como Aceptado. Cuando todos estén aceptados, avanza a la visita.',
        \App\Enums\ApplicationStatus::Location => 'Ahora: visita el domicilio (prueba de vista) y confirma que vive ahí. No se aprueba desde el escritorio sin la visita.',
        \App\Enums\ApplicationStatus::Preapproved => 'Ahora: dar la aprobación final para firmar contrato y entregar la moto.',
        \App\Enums\ApplicationStatus::Approved => 'Solicitud aprobada. Contrato y cuotas listos.',
        \App\Enums\ApplicationStatus::Rejected => 'Solicitud rechazada.',
    };
@endphp
<div class="stepper-wrap">
    <p class="stepper-caption">
        Flujo del expediente.
        <strong>{{ $actionHint }}</strong>
    </p>
    <ol class="stepper">
        @foreach ($steps as $index => $step)
            @php
                $class = '';
                $tag = '';
                $skipped = $application->channel === \App\Enums\ApplicationChannel::Branch
                    && $step === \App\Enums\ApplicationStatus::Preevaluation;
                if ($skipped) {
                    $class = 'skip';
                    $tag = 'No aplica';
                } elseif ($current === \App\Enums\ApplicationStatus::Rejected) {
                    $class = '';
                } elseif ($position !== false && $index < $position) {
                    $class = 'done';
                    $tag = 'Listo';
                } elseif ($step === $current) {
                    $class = 'on';
                    $tag = 'Ahora';
                } else {
                    $tag = 'Siguiente';
                }
            @endphp
            <li class="{{ $class }}">
                @if ($tag)<span class="step-tag">{{ $tag }}</span>@endif
                {{ $step->label() }}
            </li>
        @endforeach
    </ol>
</div>
@if ($current === \App\Enums\ApplicationStatus::Rejected)
    <div class="flash error">Rechazada. {{ $application->rejection_reason }}</div>
@endif
