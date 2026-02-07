<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Facility;

class FacilityAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        if ($user->hasRole(['admin', 'hrrd'])) {
            return $next($request);
        }
        $facilityId = $request->route('facility');
        if ($facilityId instanceof Facility) {
            $facilityId = $facilityId->id;
        }
        if (!$facilityId) {
            return $next($request);
        }
        if ($user->hasRole(['facility-admin', 'facility-dsd', 'facility-editor'])) {
            if (!$user->canManageFacility($facilityId)) {
                abort(403, 'You do not have permission to access this facility.');
            }
        }
        return $next($request);
    }
}
