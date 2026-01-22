<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class TenantServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('currentTenant', function () {
            return null;
        });
    }

    public function boot(): void
    {
        //
    }
}
