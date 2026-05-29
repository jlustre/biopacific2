<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Register;
use App\Models\RegistrationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_with_valid_registration_code(): void
    {
        $registrationCode = RegistrationCode::create([
            'code' => 'E-TEST01',
            'type' => RegistrationCode::TYPE_EMPLOYEE,
            'employee_num' => 'EMP001',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'ssn_last4' => '1234',
            'expires_at' => now()->addDays(7),
        ]);

        $response = Livewire::test(Register::class)
            ->set('registrationCode', $registrationCode->code)
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('identityVerification', '1234')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $response
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard.index', absolute: false));

        $this->assertAuthenticated();
        $this->assertNotNull($registrationCode->fresh()->used_at);
    }

    public function test_registration_is_rejected_without_valid_code(): void
    {
        Livewire::test(Register::class)
            ->set('registrationCode', 'E-BADCODE')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('identityVerification', '1234')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register')
            ->assertHasErrors(['registrationCode']);
    }
}
