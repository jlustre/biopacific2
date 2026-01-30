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
        $facility = Facility::where('domain', $host)->first();

        if ($facility) {
            // Share the facility data with all views
            view()->share('currentFacility', $facility);

            // Internally dispatch to the facility public controller action
            // This will render the facility public page without changing the URL
            return app()->call('App\\Http\\Controllers\\FacilityController@publicView', ['facility' => $facility]);
        }

        return $next($request);
    }
}
