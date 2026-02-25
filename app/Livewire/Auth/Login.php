<?php

namespace App\Livewire\Auth;

use App\Models\JobApplication;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    public ?string $applicantCode = null;

    public function mount($code = null): void
    {
        $this->applicantCode = $code ? trim($code) : null;
        if ($this->applicantCode) {
            $jobApplication = JobApplication::where('applicant_code', $this->applicantCode)->first();
            if ($jobApplication) {
                $this->email = (string) $jobApplication->email;
            }
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        Session::regenerate();

        $user = Auth::user();
        
        // If logged in with applicant code, redirect to pre-employment portal
        if ($this->applicantCode) {
            $this->redirect(route('pre-employment.index', ['code' => $this->applicantCode], absolute: false), navigate: true);
            return;
        }
        
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            $this->redirectIntended(default: route('admin.dashboard.index', absolute: false), navigate: true);
        } else {
            // Clear any intended URL to prevent redirecting to /admin/dashboard
            session()->forget('url.intended');
            $this->redirect(route('dashboard.index', absolute: false), navigate: true);
        }
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
