<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class InternalLoginController extends Controller
{
    private $tempPassword = 'TempPass2025!'; // Change as needed

    public function showLoginForm()
    {
        return view('internal-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        if ($request->password === $this->tempPassword) {
            Session::put('internal_authenticated', true);
            return redirect()->intended('/');
        }

        return back()->with('error', 'Invalid password.');
    }

    public function logout()
    {
        Session::forget('internal_authenticated');
        return redirect()->route('internal.login.form');
    }
}
