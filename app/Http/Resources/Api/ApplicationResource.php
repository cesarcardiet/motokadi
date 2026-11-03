<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\CreditApplication */
class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing(['motorcycle', 'branch', 'documents', 'contract.motorcycle', 'events.actor']);

        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'channel' => $this->channel->value,
            'color' => $this->color,
            'city' => $this->city,
            'address' => $this->address,
            'whatsapp_phone' => $this->whatsapp_phone,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'location_verified_at' => $this->location_verified_at?->toIso8601String(),
            'price' => (float) $this->price,
            'initial_payment' => (float) $this->initial_payment,
            'balance' => (float) $this->balance,
            'monthly_payment' => (float) $this->monthly_payment,
            'term_months' => $this->term_months,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'motorcycle' => new MotorcycleResource($this->motorcycle),
            'branch' => $this->branch?->only(['id', 'name', 'city', 'address', 'phone']),
            'documents' => $this->documents->map(fn ($doc) => [
                'id' => $doc->id,
                'type' => $doc->type->value,
                'type_label' => $doc->type->label(),
                'status' => $doc->status->value,
                'status_label' => $doc->status->label(),
                'original_name' => $doc->original_name,
                'url' => $doc->path ? Storage::disk('public')->url($doc->path) : null,
                'review_note' => $doc->review_note,
            ])->values(),
            'contract' => $this->contract ? new ContractResource($this->contract) : null,
            'events' => $this->events->map(fn ($event) => [
                'id' => $event->id,
                'note' => $event->note,
                'actor' => $event->actor?->name,
                'created_at' => $event->created_at?->toIso8601String(),
            ])->values(),
        ];
    }
}
