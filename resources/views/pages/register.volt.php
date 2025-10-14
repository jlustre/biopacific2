<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

return fn () => volt()->layout('components.layouts.auth')->render(function () {
    return [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'register' => function ($state) {
            $state->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:8',
            ]);

            $user = User::create([
                'name' => $state->name,
                'email' => $state->email,
                'password' => Hash::make($state->password),
            ]);

            Auth::login($user);
            session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        },
    ];
});
