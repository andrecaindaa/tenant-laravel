<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
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
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function roleInTenant($tenantId = null): ?string
    {
        $tenantId ??= session('tenant_id');

        if (!$tenantId) {
            return null;
        }

        return $this->tenants()
            ->where('tenants.id', $tenantId)
            ->first()
            ?->pivot
            ?->role;
    }

    public function permissionsInTenant($tenantId = null): array
    {
        $role = $this->roleInTenant($tenantId);

        if (!$role) {
            return [];
        }

        return config("roles.$role", []);
    }

}
