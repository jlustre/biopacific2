<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeIsActive
{
    /**
     * Block portal access for users linked to a terminated/suspended employee.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user || ! method_exists($user, 'isBlockedDueToInactiveEmployee')) {
            return $next($request);
        }

        if (! $user->isBlockedDueToInactiveEmployee()) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'Your employee account is inactive. Contact HR if you believe this is an error.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()
            ->route('login')
            ->withErrors(['email' => $message]);
    }

    protected function shouldPassThrough(Request $request): bool
    {
        if ($request->routeIs(
            'login',
            'login.*',
            'logout',
            'register',
            'register.*',
            'password.*',
            'verification.*',
            'admin.login',
            'admin.login.*',
            'admin.logout',
            'admin.mfa.*',
            'livewire.*',
            'default.livewire.update',
            'livewire.update.admin',
        )) {
            return true;
        }

        if ($request->is(
            'login',
            'register',
            'forgot-password',
            'reset-password/*',
            'verify-email',
            'verify-email/*',
            'email/verification-notification',
            'admin/login',
            'admin/logout',
            'livewire/*',
        )) {
            return true;
        }

        return ! $this->routeRequiresAuthentication($request);
    }

    protected function routeRequiresAuthentication(Request $request): bool
    {
        $route = $request->route();

        if (! $route) {
            return false;
        }

        return collect($route->gatherMiddleware())->contains(
            fn (string $middleware) => $middleware === 'auth'
                || str_starts_with($middleware, 'auth:')
        );
    }
}
