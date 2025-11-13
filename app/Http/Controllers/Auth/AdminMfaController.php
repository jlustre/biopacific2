<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class AdminMfaController extends Controller
{
    public function showMfaForm(Request $request)
    {
        // Only show if user is authenticated but not yet MFA verified
    if (!Auth::guard('admin')->check() || !$request->session()->get('mfa_pending')) {
            return redirect()->route('admin.login');
        }
        return view('auth.admin-mfa');
    }

    public function verifyMfa(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|digits:6',
        ]);
    $user = Auth::guard('admin')->user();
        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->input('one_time_password'));
        if ($valid) {
            $request->session()->forget('mfa_pending');
            $request->session()->put('mfa_verified', true);
            return redirect()->intended(route('admin.dashboard.index'));
        }
        return back()->withErrors(['one_time_password' => 'Invalid authentication code.']);
    }
}
