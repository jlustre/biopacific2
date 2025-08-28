<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Facility;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $domain = $request->getHost();

        // Remove www. if present
        $domain = preg_replace('/^www\./', '', $domain);

        // For local development, handle localhost with different ports or subdomains
        if (app()->environment('local')) {
            // Handle cases like facility1.localhost or localhost:8000/facility1
            if (strpos($domain, '.localhost') !== false) {
                $subdomain = explode('.', $domain)[0];
                $facility = Facility::where('subdomain', $subdomain)
                                  ->where('is_active', true)
                                  ->first();
            } else {
                // For development, use first facility if no specific domain match
                $facility = Facility::where('is_active', true)->first();
            }
        } else {
            $facility = Facility::findByDomain($domain);
        }

        if (!$facility) {
            // For debugging in development
            if (app()->environment('local')) {
                // Try to get any active facility for development
                $facility = Facility::where('is_active', true)->first();

                if (!$facility) {
                    abort(404, 'No active facilities found in database. Please seed facilities first.');
                }

                // For localhost development, temporarily use this facility
                logger('ResolveTenant: Using fallback facility for localhost development: ' . $facility->name);
            } else {
                abort(404, 'Facility not found for domain: ' . $domain);
            }
        }

        // Set global tenant
        app()->instance('current_facility', $facility);

        // Share with all views
        View::share('facility', $facility->toArray());

        // Set layout template for views (it's a string field, not a relationship)
<<<<<<< HEAD
        View::share('layoutTemplate', $facility->layout_template ?? 'default-template');
=======
        View::share('layoutTemplate', $facility->layout_template ?? 'layout1');
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be

        return $next($request);
    }
}
