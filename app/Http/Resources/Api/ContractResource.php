<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\FinancingContract */
class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing(['motorcycle', 'installments.vouchers']);

        return [
            'id' => $this->id,
            'contract_number' => $this->contract_number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'signed_at' => $this->signed_at?->toIso8601String(),
            'term_months' => $this->term_months,
            'monthly_payment' => (float) $this->monthly_payment,
            'balance' => (float) $this->balance,
            'unit_assigned' => $this->hasAssignedUnit(),
            'unit' => $this->hasAssignedUnit() ? [
                'cartel' => $this->unit_cartel,
                'plate' => $this->unit_plate,
                'serial' => $this->unit_serial,
                'assigned_at' => $this->unit_assigned_at?->toIso8601String(),
            ] : null,
            'motorcycle' => new MotorcycleResource($this->motorcycle),
            'installments' => $this->whenLoaded('installments', fn () => $this->installments->map(fn ($row) => [
                'id' => $row->id,
                'number' => $row->number,
                'due_date' => $row->due_date?->toDateString(),
                'amount' => (float) $row->amount,
                'status' => $row->status->value,
                'status_label' => $row->status->label(),
                'paid_at' => $row->paid_at?->toIso8601String(),
                'days_late' => $row->days_late,
                'late_fee' => (float) $row->late_fee,
                'vouchers' => $row->vouchers->map(fn ($v) => [
                    'id' => $v->id,
                    'status' => $v->status->value,
                    'status_label' => $v->status->label(),
                    'reference' => $v->reference,
                    'paid_on' => $v->paid_on?->toDateString(),
                    'amount' => (float) $v->amount,
                    'pdf_url' => url('/api/v1/vouchers/'.$v->id.'/pdf'),
                ])->values(),
            ])->values()),
        ];
    }
}
