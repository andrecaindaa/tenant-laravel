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
            return response()->json([
                'message' => 'No tenant selected'
            ], 403);
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json([
                'message' => 'Invalid tenant'
            ], 403);
        }

        // Guardar o tenant no container
        app()->instance('currentTenant', $tenant);

        return $next($request);
    }
}
