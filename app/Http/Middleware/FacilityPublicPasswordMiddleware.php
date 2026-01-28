<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FacilityPublicPasswordMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $facility = $request->route('facility');
        $slug = is_object($facility) ? $facility->slug : $facility;
        $sessionKey = 'facility_public_authenticated_' . $slug;
        if (!$request->session()->get($sessionKey)) {
            return redirect()->route('facility.public.login', ['facility' => $slug]);
        }
        return $next($request);
    }
}
