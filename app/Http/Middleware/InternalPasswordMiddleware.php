<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class InternalPasswordMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::get('internal_authenticated')) {
            return redirect()->route('internal.login.form');
        }
        return $next($request);
    }
}
