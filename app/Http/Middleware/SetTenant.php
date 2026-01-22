<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Tenant;

class SetTenant
{
    public function handle(Request $request, Closure $next)
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('select-tenant');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('select-tenant');
        }

        app()->instance('currentTenant', $tenant);

        return $next($request);
    }
}
