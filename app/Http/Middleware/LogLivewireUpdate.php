<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogLivewireUpdate
{
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('local') && $request->is('livewire/update')) {
            $components = $request->input('components', []);
            $first = $components[0] ?? null;
            $snapshot = $first['snapshot'] ?? null;

            $componentName = null;
            if (is_string($snapshot)) {
                $decoded = json_decode($snapshot, true);
                $componentName = $decoded['memo']['name'] ?? null;
            }

            Log::info('Livewire update request', [
                'component_count' => is_array($components) ? count($components) : 0,
                'first_component' => $componentName,
                'has_snapshot' => is_string($snapshot),
            ]);
        }

        return $next($request);
    }
}