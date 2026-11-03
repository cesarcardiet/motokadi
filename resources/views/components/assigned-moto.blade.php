@props(['contract', 'compact' => false])
@php
    $moto = $contract->motorcycle;
    $assigned = $contract->hasAssignedUnit();
@endphp
<article @class(['assigned-moto', 'assigned-moto-compact' => $compact, 'is-pending' => ! $assigned])>
    <div class="assigned-moto-photo">
        @if ($moto?->photoUrl())
            <img src="{{ $moto->photoUrl() }}" alt="{{ $moto->name }}">
        @else
            <div class="photo-missing">Sin foto</div>
        @endif
    </div>
    <div class="assigned-moto-body">
        <p class="kicker">{{ $assigned ? 'Moto asignada' : 'Modelo del financiamiento' }}</p>
        <h3>{{ $moto?->name ?? 'Moto' }}</h3>
        @if ($moto)
            <span class="cc-inline">{{ $moto->displacement }}CC</span>
            <p class="muted">{{ $moto->lineLabel() }} · {{ $moto->summary }}</p>
        @endif
        <p class="muted">Contrato {{ $contract->contract_number }}</p>

        @if ($assigned)
            <dl class="unit-ids">
                <div><dt>Cartel</dt><dd>{{ $contract->unit_cartel }}</dd></div>
                <div><dt>Placa</dt><dd>{{ $contract->unit_plate }}</dd></div>
                <div><dt>Serial</dt><dd>{{ $contract->unit_serial }}</dd></div>
            </dl>
            @if ($contract->unit_assigned_at)
                <p class="muted tiny">Asignada {{ $contract->unit_assigned_at->format('d/m/Y') }}</p>
            @endif
        @else
            <p class="pill pill-warn">Pendiente de asignación de unidad</p>
            <p class="muted">Cartel, placa y serial se cargan al aprobar y entregar la moto.</p>
        @endif
    </div>
</article>
