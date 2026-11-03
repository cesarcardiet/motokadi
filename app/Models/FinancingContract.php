<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancingContract extends Model
{
    protected $fillable = [
        'credit_application_id',
        'user_id',
        'motorcycle_id',
        'branch_id',
        'contract_number',
        'status',
        'signed_at',
        'start_date',
        'term_months',
        'monthly_payment',
        'balance',
        'unit_cartel',
        'unit_plate',
        'unit_serial',
        'unit_assigned_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ContractStatus::class,
            'signed_at' => 'datetime',
            'start_date' => 'date',
            'monthly_payment' => 'decimal:2',
            'balance' => 'decimal:2',
            'unit_assigned_at' => 'datetime',
        ];
    }

    public function hasAssignedUnit(): bool
    {
        return filled($this->unit_cartel)
            && filled($this->unit_plate)
            && filled($this->unit_serial)
            && $this->unit_assigned_at !== null;
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(CreditApplication::class, 'credit_application_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function motorcycle(): BelongsTo
    {
        return $this->belongsTo(Motorcycle::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('number');
    }
}
