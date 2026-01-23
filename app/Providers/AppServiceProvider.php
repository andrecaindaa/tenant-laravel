<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Compartilha o tenant com todas as views
        View::composer('*', function ($view) {
            $tenant = app('currentTenant');
            $view->with('tenant', $tenant);

            // Também compartilha informações do usuário
            if (Auth::check()) {
                $view->with('authUser', Auth::user());
            }
        });
    }
}
