<?php

namespace App\Models;

use App\Enums\VoucherStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentVoucher extends Model
{
    protected $fillable = [
        'installment_id',
        'user_id',
        'path',
        'original_name',
        'amount',
        'exchange_rate',
        'amount_ves',
        'rate_frozen_at',
        'paid_on',
        'reference',
        'status',
        'review_note',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'amount_ves' => 'decimal:2',
            'rate_frozen_at' => 'datetime',
            'paid_on' => 'date',
            'status' => VoucherStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function frozenRate(): ?float
    {
        return $this->exchange_rate !== null ? (float) $this->exchange_rate : null;
    }

    public function amountInVes(): float
    {
        if ($this->amount_ves !== null) {
            return (float) $this->amount_ves;
        }

        $rate = $this->frozenRate();
        if ($rate === null) {
            return 0.0;
        }

        return round((float) $this->amount * $rate, 2);
    }

    public function frozenRateLabel(): string
    {
        $rate = $this->frozenRate();
        if ($rate === null) {
            return 'Sin tasa congelada';
        }

        return Money::rate($rate);
    }

    public function frozenRateWhenLabel(): string
    {
        $when = $this->rate_frozen_at ?? $this->created_at;

        return $when?->format('d/m/Y H:i') ?? '—';
    }
}
