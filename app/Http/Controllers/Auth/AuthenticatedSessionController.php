<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
    $request->authenticate();
    session()->regenerate();

        $user = Auth::user();
        $isAdmin = $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : ($user && $user->roles && $user->roles->pluck('name')->contains('admin'));

        if ($isAdmin) {
            // Only redirect to /admin routes if the intended URL is an admin page
            $intended = session()->pull('url.intended');
            if ($intended && str_contains($intended, '/admin')) {
                return redirect()->to($intended);
            }
            // Otherwise, go to user dashboard
            return redirect()->route('admin.dashboard.index');
        }
        // All other users always go to /dashboard, never to any /admin route
        return redirect()->route('admin.dashboard.index');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
