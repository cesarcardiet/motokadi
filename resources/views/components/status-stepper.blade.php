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
@endphp
<ol class="stepper">
    @foreach ($steps as $index => $step)
        @php
            $class = '';
            $skipped = $application->channel === \App\Enums\ApplicationChannel::Branch
                && $step === \App\Enums\ApplicationStatus::Preevaluation;
            if ($skipped) {
                $class = 'skip';
            } elseif ($current === \App\Enums\ApplicationStatus::Rejected) {
                $class = '';
            } elseif ($position !== false && $index < $position) {
                $class = 'done';
            } elseif ($step === $current) {
                $class = 'on';
            }
        @endphp
        <li class="{{ $class }}">{{ $step->label() }}</li>
    @endforeach
</ol>
@if ($current === \App\Enums\ApplicationStatus::Rejected)
    <div class="flash error">Rechazada. {{ $application->rejection_reason }}</div>
@endif
