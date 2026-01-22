<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function ($user, $ability) {
            $permissions = $user->permissionsInTenant();

            return in_array($ability, $permissions) ? true : null;
        });
    }
}
