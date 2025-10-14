<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

return fn () => volt()->layout('components.layouts.auth')->render(function () {
    return [
        'email' => '',
        'password' => '',
        'remember' => false,
        'login' => function ($state) {
            $state->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if (!Auth::attempt([
                'email' => $state->email,
                'password' => $state->password,
            ], $state->remember)) {
                throw ValidationException::withMessages([
                    'email' => __('auth.failed'),
                ]);
            }

            session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        },
    ];
});
