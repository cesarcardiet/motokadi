<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'national_id',
        'role_id',
        'branch_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(CreditApplication::class);
    }

    public function hasPermission(string $slug): bool
    {
        $this->loadMissing('role.permissions');

        return (bool) $this->role?->permissions->contains(
            fn (Permission $permission) => $permission->slug === $slug
        );
    }

    public function dashboardRoute(): string
    {
        if ($this->hasPermission('panel.admin')) {
            return route('admin.dashboard');
        }

        if ($this->hasPermission('solicitudes.revisar')) {
            return route('staff.applications.index');
        }

        return route('client.applications.index');
    }
}
