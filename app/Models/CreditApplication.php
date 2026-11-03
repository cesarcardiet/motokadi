<?php

namespace App\Models;

use App\Enums\ApplicationChannel;
use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditApplication extends Model
{
    protected $fillable = [
        'user_id',
        'motorcycle_id',
        'branch_id',
        'channel',
        'status',
        'color',
        'price',
        'initial_payment',
        'balance',
        'monthly_payment',
        'term_months',
        'address',
        'city',
        'latitude',
        'longitude',
        'location_verified_at',
        'rejection_reason',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'channel' => ApplicationChannel::class,
            'status' => ApplicationStatus::class,
            'price' => 'decimal:2',
            'initial_payment' => 'decimal:2',
            'balance' => 'decimal:2',
            'monthly_payment' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'location_verified_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
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

    public function documents(): HasMany
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ApplicationEvent::class)->latest();
    }

    public function scopeForActor(Builder $query, User $actor): Builder
    {
        if ($actor->hasPermission('panel.admin')) {
            return $query;
        }

        return $query->where('branch_id', $actor->branch_id);
    }
}
