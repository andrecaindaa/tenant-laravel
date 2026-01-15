<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Declara o binding, mesmo antes de existir tenant
        $this->app->bind('currentTenant', function () {
            return null;
        });
    }

    public function boot(): void
    {
        //
    }
}
