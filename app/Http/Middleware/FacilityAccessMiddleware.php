<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Facility;

class FacilityAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Web admins and regular admins can access everything
        if ($user->hasRole(['web-admin', 'admin'])) {
            return $next($request);
        }

        // Check if route has facility parameter
        $facilityId = $request->route('facility');
        
        // If facility is passed as a model, get its ID
        if ($facilityId instanceof Facility) {
            $facilityId = $facilityId->id;
        }

        // If no facility specified in route, allow access (general pages)
        if (!$facilityId) {
            return $next($request);
        }

        // For facility-specific routes, check if user can manage this facility
        if ($user->hasRole(['facility-admin', 'facility-editor'])) {
            if (!$user->canManageFacility($facilityId)) {
                abort(403, 'You do not have permission to access this facility.');
            }
        }

        return $next($request);
    }
}
