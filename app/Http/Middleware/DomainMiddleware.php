<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Facility;

class DomainMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost(); // e.g., 'almadenhrc.com'
    
    // Look up the facility in your database by the domain
    $facility = Facility::where('domain', $host)->first();

    if ($facility) {
        // Share the facility data with all views
        view()->share('currentFacility', $facility);
    }

    return $next($request);
    }
}
