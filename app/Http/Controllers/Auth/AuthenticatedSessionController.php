<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
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
    public function create(Request $request): View
    {
        $prefillEmail = '';

        if ($request->filled('c')) {
            $jobApplication = JobApplication::where('applicant_code', $request->string('c')->trim())->first();

            if ($jobApplication) {
                $prefillEmail = (string) $jobApplication->email;
            }
        }

        return view('auth.login', compact('prefillEmail'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
    $request->authenticate();
    session()->regenerate();

        $user = Auth::user();
        $hasRole = $user && method_exists($user, 'hasRole')
            ? fn (string|array $roles) => $user->hasRole($roles)
            : fn () => false;

        $intended = session()->pull('url.intended');
        if ($intended) {
            return redirect()->to($intended);
        }

        // Facility leaders land on the personal work-queue dashboard
        if ($hasRole(['facility-admin', 'facility-dsd', 'don', 'ssd', 'activities-director', 'facility-ssd'])) {
            return redirect()->route('dashboard.index');
        }

        if ($hasRole(['admin', 'super-admin'])) {
            return redirect()->route('admin.dashboard.index');
        }

        if ($hasRole(['rdhr', 'regional-director'])) {
            return redirect()->route('admin.dashboard.index');
        }

        return redirect()->route('dashboard.index');
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
