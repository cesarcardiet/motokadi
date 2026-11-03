<?php

namespace App\Services\Financing;

use App\Enums\ApplicationStatus;
use App\Enums\ContractStatus;
use App\Enums\InstallmentStatus;
use App\Models\CreditApplication;
use App\Models\FinancingContract;
use App\Models\Installment;
use App\Services\Applications\WorkflowException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContractService
{
    /**
     * @param  array{unit_cartel: string, unit_plate: string, unit_serial: string}|null  $unit
     */
    public function activateFromApproval(
        CreditApplication $application,
        ?Carbon $signedAt = null,
        ?array $unit = null,
    ): FinancingContract {
        if ($application->status !== ApplicationStatus::Approved) {
            throw new WorkflowException('Solo se firma contrato cuando la solicitud está aprobada.');
        }

        if ($application->contract()->exists()) {
            $existing = $application->contract()->with('installments')->firstOrFail();
            if ($unit !== null && ! $existing->hasAssignedUnit()) {
                return $this->assignUnit($existing, $unit);
            }

            return $existing;
        }

        $signedAt ??= now();
        $assignedAt = $unit !== null ? $signedAt->copy() : null;

        return DB::transaction(function () use ($application, $signedAt, $unit, $assignedAt) {
            $contract = FinancingContract::query()->create([
                'credit_application_id' => $application->id,
                'user_id' => $application->user_id,
                'motorcycle_id' => $application->motorcycle_id,
                'branch_id' => $application->branch_id,
                'contract_number' => sprintf('KM-%s-%04d', $signedAt->format('Ymd'), $application->id),
                'status' => ContractStatus::Active,
                'signed_at' => $signedAt,
                'start_date' => $signedAt->toDateString(),
                'term_months' => $application->term_months,
                'monthly_payment' => $application->monthly_payment,
                'balance' => $application->balance,
                'unit_cartel' => $unit['unit_cartel'] ?? null,
                'unit_plate' => $unit['unit_plate'] ?? null,
                'unit_serial' => $unit['unit_serial'] ?? null,
                'unit_assigned_at' => $assignedAt,
            ]);

            for ($number = 1; $number <= $application->term_months; $number++) {
                $contract->installments()->create([
                    'number' => $number,
                    'due_date' => $signedAt->copy()->addMonths($number)->toDateString(),
                    'amount' => $application->monthly_payment,
                    'status' => InstallmentStatus::Pending,
                ]);
            }

            return $contract->load('installments');
        });
    }

    /**
     * @param  array{unit_cartel: string, unit_plate: string, unit_serial: string}  $unit
     */
    public function assignUnit(FinancingContract $contract, array $unit, ?Carbon $assignedAt = null): FinancingContract
    {
        if ($contract->hasAssignedUnit()) {
            throw new WorkflowException('Esta moto ya tiene cartel, placa y serial asignados.');
        }

        $contract->update([
            'unit_cartel' => $unit['unit_cartel'],
            'unit_plate' => $unit['unit_plate'],
            'unit_serial' => $unit['unit_serial'],
            'unit_assigned_at' => $assignedAt ?? now(),
        ]);

        return $contract->fresh(['installments', 'motorcycle']);
    }

    /**
     * Seed helper: mark schedule with paid / mora states for demos.
     *
     * @param  array<int, array{status: InstallmentStatus, paid_at?: ?Carbon, days_late?: int, late_fee?: float|string}>  $overrides
     */
    public function applySchedule(FinancingContract $contract, array $overrides): void
    {
        foreach ($overrides as $number => $data) {
            /** @var Installment|null $row */
            $row = $contract->installments()->where('number', $number)->first();
            if (! $row) {
                continue;
            }

            $row->update([
                'status' => $data['status'],
                'paid_at' => $data['paid_at'] ?? null,
                'days_late' => $data['days_late'] ?? 0,
                'late_fee' => $data['late_fee'] ?? 0,
            ]);
        }
    }
}
