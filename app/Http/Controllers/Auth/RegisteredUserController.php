<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $prefillName = '';
        $prefillEmail = '';

        if ($request->filled('c')) {
            $jobApplication = JobApplication::where('applicant_code', $request->string('c')->trim())->first();

            if ($jobApplication) {
                $prefillName = trim($jobApplication->first_name . ' ' . $jobApplication->last_name);
                $prefillEmail = (string) $jobApplication->email;
            }
        }

        return view('auth.register', compact('prefillName', 'prefillEmail'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // If applicant_code is present, link job application to user
        if ($request->filled('c')) {
            $jobApplication = JobApplication::where('applicant_code', $request->string('c')->trim())->first();
            if ($jobApplication) {
                $jobApplication->user_id = $user->id;
                $jobApplication->save();
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
