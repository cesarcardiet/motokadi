<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->loadMissing('role.permissions', 'branch');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'national_id' => $this->national_id,
            'role' => $this->role?->slug,
            'role_label' => $this->role?->name,
            'branch_id' => $this->branch_id,
            'branch' => $this->branch?->only(['id', 'name', 'city']),
            'permissions' => $this->role?->permissions->pluck('slug')->values() ?? [],
            'is_admin' => $this->hasPermission('panel.admin'),
            'is_client' => $this->hasPermission('solicitudes.crear') && ! $this->hasPermission('solicitudes.revisar'),
        ];
    }
}
