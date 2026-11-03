<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Motorcycle */
class MotorcycleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $quote = $this->quote();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'line' => $this->line,
            'line_label' => $this->lineLabel(),
            'displacement' => $this->displacement,
            'summary' => $this->summary,
            'price' => (float) $this->price,
            'initial_payment' => (float) $this->initial_payment,
            'term_months' => $this->term_months,
            'colors' => $this->colors ?? [],
            'photo_url' => $this->photoUrl(),
            'quote' => [
                'price' => (float) $quote['price'],
                'initial' => (float) $quote['initial'],
                'balance' => (float) $quote['balance'],
                'monthly' => (float) $quote['monthly'],
                'months' => (int) $quote['months'],
            ],
        ];
    }
}
