<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Models\JobApplication;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $applicantCode = null;

    /**
     * Mount the component and prefill data if applicant code is provided.
     */
    public function mount($code = null): void
    {
        $this->applicantCode = $code ? trim($code) : null;
        if ($this->applicantCode) {
            $jobApplication = JobApplication::where('applicant_code', $this->applicantCode)->first();
            
            if ($jobApplication) {
                $this->name = trim($jobApplication->first_name . ' ' . $jobApplication->last_name);
                $this->email = $jobApplication->email;
            }
        }
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        // If registered with applicant code, link the job application to this user
        if ($this->applicantCode) {
            $jobApplication = JobApplication::where('applicant_code', $this->applicantCode)->first();
            if ($jobApplication) {
                $jobApplication->user_id = $user->id;
                $jobApplication->save();
            }
            $this->redirect(route('pre-employment.index', ['code' => $this->applicantCode], absolute: false), navigate: true);
        } else {
            $this->redirect(route('dashboard.index', absolute: false), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
