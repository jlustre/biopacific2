<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminAuthenticatedSessionController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function create(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

    if (!Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => trans('auth.failed'),
            ])->withInput();
        }

        $request->session()->regenerate();

        // MFA enforcement for admins with google2fa_secret
        $user = Auth::guard('admin')->user();
        if ($user && $user->google2fa_secret) {
            $request->session()->put('mfa_pending', true);
            Auth::guard('admin')->logout(); // Log out to require MFA verification
            $request->session()->save();
            return redirect()->route('admin.mfa.form');
        }

        return redirect()->intended(route('admin.dashboard.index', absolute: false));
    }

    /**
     * Logout admin.
     */
    public function destroy(Request $request): RedirectResponse
    {
    Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/admin/login');
    }
}
