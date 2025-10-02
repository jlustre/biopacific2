<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Facility;

class CheckFacilityShutdown
{
    public function handle(Request $request, Closure $next)
    {
        // Check global shutdown
        $global = DB::table('global_shutdowns')->orderByDesc('id')->first();
        if ($global && $global->is_shutdown) {
            return response()->view('shutdown', [
                'message' => $global->shutdown_message,
                'eta' => $global->shutdown_eta,
                'isGlobal' => true,
            ]);
        }

        // Check per-facility shutdown (assuming route has facility or subdomain logic)
        $facility = null;
        $routeFacility = $request->route('facility');
        if ($routeFacility instanceof Facility) {
            $facility = $routeFacility;
        } elseif (is_string($routeFacility)) {
            $facility = Facility::where('slug', $routeFacility)->first();
        } elseif ($request->user() && $request->user()->facility_id) {
            $facility = Facility::find($request->user()->facility_id);
        }

        if ($facility && $facility->is_shutdown) {
            return response()->view('shutdown', [
                'message' => $facility->shutdown_message,
                'eta' => $facility->shutdown_eta,
                'isGlobal' => false,
            ]);
        }

        return $next($request);
    }
}
