<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null): Response
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user ||
            (! $user instanceof MustVerifyEmail) ||
            $user->hasVerifiedEmail()) {
            return $next($request);
        }

        return $request->expectsJson()
            ? abort(403, 'Your email address is not verified.')
            : Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
    }

    protected function shouldPassThrough(Request $request): bool
    {
        if ($request->routeIs(
            'verification.*',
            'livewire.*',
            'default.livewire.update',
            'livewire.update.admin',
            'logout',
        )) {
            return true;
        }

        if ($request->is(
            'verify-email',
            'verify-email/*',
            'email/verification-notification',
            'livewire/*',
        )) {
            return true;
        }

        if ($this->isMemberSelfServiceRoute($request)) {
            return true;
        }

        return ! $this->routeRequiresAuthentication($request);
    }

    protected function isMemberSelfServiceRoute(Request $request): bool
    {
        return $request->routeIs([
            'dashboard.index',
            'user.dashboard',
            'member.facility.dashboard',
            'user.hr-portal',
            'hr-portal.index',
            'admin.facility.dashboard',
            'member.documents',
            'member.certifications',
            'member.trainings',
            'member.news-events.index',
            'member.facilities.websites',
            'member.facilities.websites.show',
            'settings.profile',
            'settings.profile.update',
            'settings.profile.avatar.update',
            'settings.profile.avatar.destroy',
            'settings.profile.emergency-contacts.store',
            'settings.profile.emergency-contacts.update',
            'settings.profile.emergency-contacts.destroy',
            'settings.profile.emergency-contacts.primary',
            'settings.profile.submit-hr-review',
            'settings.password',
            'settings.password.update',
            'settings.appearance',
            'pre-employment.portal',
            'pre-employment.reference-checks.add',
            'pre-employment.reference-checks.save',
            'pre-employment.reference-checks.delete',
            'pre-employment.checklist.update',
            'pre-employment.checklist.return',
            'pre-employment.checklist.approve',
            'employment.portal',
        ]);
    }

    protected function routeRequiresAuthentication(Request $request): bool
    {
        $route = $request->route();

        if (! $route) {
            return false;
        }

        return collect($route->gatherMiddleware())->contains(
            fn (string $middleware) => $middleware === 'auth' || str_starts_with($middleware, 'auth:')
        );
    }
}
