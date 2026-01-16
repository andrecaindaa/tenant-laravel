<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantLimit
{
    public function handle($request, Closure $next, string $limit)
    {
        $tenant = app('currentTenant');
        $max = $tenant->limit($limit);

        if ($max !== null) {
            $count = match ($limit) {
                'projects' => \App\Models\Project::count(),
                default => 0,
            };

            if ($count >= $max) {
                abort(403, 'Plan limit reached');
            }
        }

        return $next($request);
    }
}
