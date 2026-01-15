<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use App\Scopes\TenantScope;

abstract class TenantAwareModel extends Model
{
    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (app()->bound('currentTenant') && app('currentTenant')) {
                $model->tenant_id = app('currentTenant')->id;
            }
        });
    }
}
