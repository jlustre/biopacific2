<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PostRegistrationMailService;
use App\Support\RegistrationCodeService;
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
        $prefillRegistrationCode = '';

        if ($request->filled('c')) {
            $code = strtoupper(trim((string) $request->string('c')));

            if (preg_match('/^[ET]-/', $code)) {
                $prefillRegistrationCode = $code;
                $record = \App\Models\RegistrationCode::query()->where('code', $code)->first();

                if ($record && $record->isUsable()) {
                    $prefillName = $record->fullName();
                    $prefillEmail = $record->email;
                }
            }
        }

        return view('auth.register', compact('prefillName', 'prefillEmail', 'prefillRegistrationCode'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'registration_code' => ['required', 'string', 'max:16'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'identity_verification' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $registrationCodeService = app(RegistrationCodeService::class);
        $codeRecord = $registrationCodeService->validateForRegistration(
            (string) $request->input('registration_code'),
            (string) $request->input('name'),
            (string) $request->input('email'),
            (string) $request->input('identity_verification', ''),
        );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $registrationCodeService->markAsUsed($codeRecord, $user);
        $registrationCodeService->linkRegisteredUser($codeRecord, $user);

        app(PostRegistrationMailService::class)->sendWelcome($user, $codeRecord);

        Auth::login($user);

        session()->put('url.intended', route('dashboard.index', absolute: false));

        return redirect()->route('dashboard.index');
    }
}
