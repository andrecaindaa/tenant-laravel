<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class SetTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        // Se não estiver autenticado, apenas partilha null
        if (!Auth::check()) {
            View::share('tenant', null);
            return $next($request);
        }

        $tenantId = session('tenant_id');

        // Auto-selecionar tenant se só existir um
        if (!$tenantId) {
            $tenants = Auth::user()->tenants;

            if ($tenants->count() === 1) {
                $tenantId = $tenants->first()->id;
                session(['tenant_id' => $tenantId]);
            }
        }

        // Se rota exige tenant e ainda não há → redirect
        if (!$tenantId && $this->requiresTenant($request)) {
            return redirect()->route('select-tenant');
        }

        // Carregar tenant
        if ($tenantId) {
            $tenant = Tenant::with('subscription.plan')->find($tenantId);

            // Segurança: validar acesso
            if (
                !$tenant ||
                !Auth::user()
                    ->tenants()
                    ->where('tenants.id', $tenantId)
                    ->exists()
            ) {
                session()->forget('tenant_id');
                return redirect()
                    ->route('select-tenant')
                    ->with('error', 'Acesso inválido ao tenant');
            }

            app()->instance('currentTenant', $tenant);
        }

        View::share('tenant', $tenant);

        return $next($request);
    }

    private function requiresTenant(Request $request): bool
    {
        $name = $request->route()?->getName();

        if (!$name) {
            return false;
        }

        return str_starts_with($name, 'dashboard')
            || str_starts_with($name, 'billing.')
            || str_starts_with($name, 'projects.')
            || str_starts_with($name, 'users.');
    }
}
