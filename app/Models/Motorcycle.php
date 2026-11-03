<?php

namespace App\Models;

use App\Services\Financing\InstallmentSimulator;
use App\Support\Catalog\KadiCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Motorcycle extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'line',
        'displacement',
        'summary',
        'price',
        'initial_payment',
        'term_months',
        'price_source',
        'colors',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'initial_payment' => 'decimal:2',
            'colors' => 'array',
            'is_active' => 'boolean',
            'term_months' => 'integer',
            'displacement' => 'integer',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CreditApplication::class);
    }

    public function lineLabel(): string
    {
        return KadiCatalog::lineLabel($this->line);
    }

    public function photoUrl(): ?string
    {
        foreach (['png', 'jpg'] as $extension) {
            $relative = 'images/motos/'.$this->slug.'.'.$extension;
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return null;
    }

    /**
     * @return array{price: string, initial: string, balance: string, monthly: string, months: int}
     */
    public function quote(): array
    {
        return app(InstallmentSimulator::class)->quote(
            $this->price,
            $this->initial_payment,
            $this->term_months,
        );
    }
}
