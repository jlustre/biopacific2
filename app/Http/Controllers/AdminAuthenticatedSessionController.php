<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminAuthenticatedSessionController extends Controller
{
    public function create()
    {
        // Return a view for admin login
        return view('admin.auth.login');
    }

    public function store(Request $request)
    {
        // Handle admin login logic
        // Example: Auth::guard('admin')->attempt($request->only('email', 'password'));
        return redirect()->route('admin.dashboard.index');
    }
}
