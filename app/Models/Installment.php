<?php

namespace App\Models;

use App\Enums\InstallmentStatus;
use App\Enums\VoucherStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Installment extends Model
{
    protected $fillable = [
        'financing_contract_id',
        'number',
        'due_date',
        'amount',
        'status',
        'paid_at',
        'days_late',
        'late_fee',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'status' => InstallmentStatus::class,
            'paid_at' => 'datetime',
            'late_fee' => 'decimal:2',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(FinancingContract::class, 'financing_contract_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(PaymentVoucher::class)->latest();
    }

    public function pendingVoucher(): ?PaymentVoucher
    {
        return $this->vouchers->firstWhere('status', VoucherStatus::Pending);
    }
}
