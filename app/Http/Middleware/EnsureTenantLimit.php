<?php

// app/Http/Middleware/EnsureTenantLimit.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTenantLimit
{
    public function handle(Request $request, Closure $next, string $key)
    {
        $tenant = app('currentTenant');

        if (!$tenant) {
            abort(400, 'Tenant not resolved');
        }

        $limit = $tenant->limit($key);

        if ($limit === null) {
            return $next($request); // ilimitado
        }

        $current = match ($key) {
            'projects' => $tenant->projects()->count(),
            'users'    => $tenant->users()->count(),
            default    => 0,
        };

        if ($current >= $limit) {
            abort(403, "Limit reached for {$key}");
        }

        return $next($request);
    }
}
