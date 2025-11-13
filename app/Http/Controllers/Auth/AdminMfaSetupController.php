<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use Illuminate\Support\Str;

class AdminMfaSetupController extends Controller
{
    public function showSetupForm(Request $request)
    {
    $user = Auth::guard('admin')->user();
        if (!$user) {
            return redirect()->route('admin.login');
        }
        if ($user->google2fa_secret) {
            return redirect()->route('admin.dashboard.index')->with('status', 'MFA is already enabled.');
        }
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();
        $qrCodeUrl = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );
        $request->session()->put('mfa_setup_secret', $secret);
        return view('auth.admin-mfa-setup', compact('secret', 'qrCodeUrl'));
    }

    public function storeSetup(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|digits:6',
        ]);
    $user = Auth::guard('admin')->user();
        if (!$user) {
            return redirect()->route('admin.login');
        }
        $secret = $request->session()->get('mfa_setup_secret');
        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($secret, $request->input('one_time_password'));
        if ($valid) {
            $user->google2fa_secret = $secret;
            $user->save();
            $request->session()->forget('mfa_setup_secret');
            return redirect()->route('admin.dashboard.index')->with('status', 'MFA enabled successfully.');
        }
        return back()->withErrors(['one_time_password' => 'Invalid authentication code.']);
    }
}
