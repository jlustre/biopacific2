<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guard = $guards[0] ?? null;
        if ($guard) {
            if (Auth::guard($guard)->check()) {
                return redirect()->route($guard === 'admin' ? 'admin.dashboard.index' : 'dashboard.index');
            }
        } else {
            if (Auth::check()) {
                return redirect()->route('dashboard.index');
            }
        }
        return $next($request);
    }
}
