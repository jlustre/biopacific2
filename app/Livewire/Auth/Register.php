<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\RegistrationCode;
use App\Support\PostRegistrationMailService;
use App\Support\RegistrationCodeService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $registrationCode = '';

    public string $name = '';

    public string $email = '';

    public string $identityVerification = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $applicantCode = null;

    public bool $requiresIdentityVerification = true;

    /**
     * Mount the component and prefill data when a registration code is provided.
     */
    public function mount($code = null): void
    {
        $code = $code ? trim((string) $code) : null;

        if ($code && preg_match('/^[ET]-/i', $code)) {
            $this->registrationCode = strtoupper($code);
            $record = RegistrationCode::query()->where('code', $this->registrationCode)->first();

            if ($record && $record->isUsable()) {
                $this->name = $record->fullName();
                $this->email = $record->email;
                $this->requiresIdentityVerification = $record->isEmployeeCode();
            }

            return;
        }

        $this->applicantCode = $code;
    }

    public function updatedRegistrationCode(string $value): void
    {
        $normalized = strtoupper(trim($value));
        $this->registrationCode = $normalized;

        $record = RegistrationCode::query()->where('code', $normalized)->first();
        $this->requiresIdentityVerification = ! $record || $record->isEmployeeCode();
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $rules = [
            'registrationCode' => ['required', 'string', 'max:16'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'identityVerification' => ['nullable', 'string', 'max:50'],
        ];

        if ($this->requiresIdentityVerification) {
            $rules['identityVerification'] = ['required', 'string', 'max:50'];
        }

        $validated = $this->validate($rules);

        $registrationCodeService = app(RegistrationCodeService::class);

        try {
            $codeRecord = $registrationCodeService->validateForRegistration(
                $validated['registrationCode'],
                $validated['name'],
                $validated['email'],
                $validated['identityVerification'] ?? '',
            );
        } catch (ValidationException $exception) {
            throw $exception;
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $registrationCodeService->markAsUsed($codeRecord, $user);
        $registrationCodeService->linkRegisteredUser($codeRecord, $user);

        app(PostRegistrationMailService::class)->sendWelcome($user, $codeRecord);

        event(new Registered($user));

        Auth::login($user);

        if ($codeRecord->type === RegistrationCode::TYPE_APPLICANT) {
            $applicationCode = $codeRecord->jobApplication?->applicant_code ?? $this->applicantCode;
            session()->put(
                'url.intended',
                route('pre-employment.index', array_filter(['code' => $applicationCode]), absolute: false)
            );
        } else {
            session()->put('url.intended', route('dashboard.index', absolute: false));
        }

        $this->redirect(route('dashboard.index', absolute: false), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
