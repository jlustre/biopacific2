<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQrCodeServiceException;
use PragmaRX\Google2FAQRCode\Google2FA as Google2FAQrCode;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;

class AdminMfaSetupController extends Controller
{
    public function showSetupForm(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->google2fa_secret) {
            return redirect()->to($this->mfaSetupRedirectTo($user))
                ->with('status', 'MFA is already enabled.');
        }

        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();
        $qrCodeUrl = $this->generateQrCodeInline($google2fa, $user->email, $secret);
        $request->session()->put('mfa_setup_secret', $secret);

        return view('auth.admin-mfa-setup', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
            'cancelUrl' => $this->mfaSetupRedirectTo($user),
        ]);
    }

    public function storeSetup(Request $request): RedirectResponse
    {
        $request->validate([
            'one_time_password' => 'required|digits:6',
        ]);

        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $secret = $request->session()->get('mfa_setup_secret');
        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($secret, $request->input('one_time_password'));
        if ($valid) {
            $user->google2fa_secret = $secret;
            $user->save();
            $request->session()->forget('mfa_setup_secret');

            return redirect()->to($this->mfaSetupRedirectTo($user))
                ->with('status', 'MFA enabled successfully.');
        }

        return back()->withErrors(['one_time_password' => 'Invalid authentication code.']);
    }

    protected function mfaSetupRedirectTo(User $user): string
    {
        if ($user->hasRole(['admin', 'super-admin'])) {
            return route('admin.dashboard.index', absolute: false);
        }

        return route('settings.profile', absolute: false);
    }

    protected function generateQrCodeInline(object $google2fa, string $email, string $secret): string
    {
        try {
            return $google2fa->getQRCodeInline(config('app.name'), $email, $secret);
        } catch (MissingQrCodeServiceException) {
            $fallback = new Google2FAQrCode(new Bacon(new SvgImageBackEnd()));

            return $fallback->getQRCodeInline(config('app.name'), $email, $secret);
        }
    }
}
