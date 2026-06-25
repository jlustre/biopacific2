<?php

namespace App\Http\Middleware;

use App\Support\FacilityShutdown;
use Closure;
use Illuminate\Http\Request;

class CheckFacilityShutdown
{
    public function handle(Request $request, Closure $next)
    {
        if (str_starts_with(trim($request->path(), '/'), 'admin/')) {
            return $next($request);
        }

        $facility = FacilityShutdown::resolveFromRequest($request);

        if ($response = FacilityShutdown::responseFor($facility)) {
            return $response;
        }

        return $next($request);
    }
}
