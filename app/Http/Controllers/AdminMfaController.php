<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminMfaController extends Controller
{
    public function showMfaForm()
    {
        // Return a view for admin MFA form
        return view('admin.mfa.form');
    }

    public function verifyMfa(Request $request)
    {
        // Handle MFA verification logic
        // Example: check MFA token and authenticate
        return redirect()->route('admin.dashboard.index');
    }
}
