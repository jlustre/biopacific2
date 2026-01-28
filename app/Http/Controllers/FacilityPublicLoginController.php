<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Facility;

class FacilityPublicLoginController extends Controller
{
    private $tempPassword = 'TempPass2025!'; // Same password for all

    public function showLoginForm(Facility $facility)
    {
        return view('facility-public-login', ['facility' => $facility]);
    }

    public function login(Request $request, Facility $facility)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $sessionKey = 'facility_public_authenticated_' . $facility->slug;
        if ($request->password === $this->tempPassword) {
            $request->session()->put($sessionKey, true);
            return redirect()->route('facility.public', ['facility' => $facility->slug]);
        }

        return back()->with('error', 'Invalid password.');
    }

    public function logout(Request $request, Facility $facility)
    {
        $sessionKey = 'facility_public_authenticated_' . $facility->slug;
        $request->session()->forget($sessionKey);
        return redirect()->route('facility.public.login', ['facility' => $facility->slug]);
    }
}
